<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\KartuInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class InventoryController extends Controller
{
    //

    public function index()
    {
        $view = view('daftar.aset-tetap');
        $view->year = getInput('year') ? getInput('year') : date('Y');
        return $view;
    }


    public function update(Request $request)
    {
        $request = $request->validate([
            'name' => 'required|string',
            'keterangan_qty_unit' => 'string',
            'date' => 'required|date',
            'nilai_perolehan' => 'required|numeric',
            'periode' => 'required|integer'
        ]);

        $inv = Inventory::update($request);
        return [
            'status' => 1,
            'msg' => $inv
        ];
    }
    public function createInventory()
    {
        return view('daftar.modal._create_inventory');
    }
    public function createKartuInventory()
    {
        return view('daftar.modal._kartu_inventory');
    }

    public function storeInventory(Request $request)
    {


        DB::beginTransaction();
        $inv = null;
        $st = null;
        try {
            $request['book_journal_id'] = session('book_journal_id');
            $request['nilai_perolehan'] = format_db($request['nilai_perolehan']);
            $request = $request->validate([
                'name' => 'required|string',
                'keterangan_qty_unit' => 'string',
                'date' => 'required|date',
                'nilai_perolehan' => 'required|numeric',
                'periode' => 'required|integer',
                'book_journal_id' => 'required|integer',
                'type_aset' => 'required|string',
                'code_group' => 'required|integer',
                'lawan_code_group' => 'required|integer',
            ]);


            $inv = Inventory::create($request);
            $inv->refresh();
            if ($inv == null) {
                throw new \Exception('Gagal menyimpan data');
            }
            $st = KartuInventory::createKartu(new Request([
                'inventory_id' => $inv->id,
                'date' => $inv->date,
                'amount' => $request['nilai_perolehan'], // ini pake format indonesia
                'type_mutasi' => 'pembelian',
                'code_group' => $request['code_group'],
                'lawan_code_group' => $request['lawan_code_group'],
            ]));

            if ($st['status'] == 0) {
                throw new \Exception($st['msg']);
            }
            DB::commit();
            return [
                'status' => 1,
                'msg' => $inv,
                'kartu' => $st['msg']
            ];
        } catch (ValidationException $e) {
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => getErrorValidation($e)
            ];
        } catch (Throwable $th) {
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
    }

    public function storeKartuInventory(Request $request)
    {

        try {
            $st = KartuInventory::createKartu($request);
            if ($st['status'] == 0) {
                throw new \Exception($st['msg']);
            }
        } catch (Throwable $th) {
            DB::rollBack();
            return ['status' => 0, 'msg' => $th->getMessage()];
        } finally {
            DB::commit();
            return [
                'status' => 1,
                'msg' => $st['msg']
            ];
        }
    }

    public function getItem()
    {
        $inv = Inventory::select('id', DB::raw('name as text'))->get();
        return [
            'results' => $inv
        ];
    }

    public function getSummary()
    {
        $year = getInput('year') ? getInput('year') : date('Y');
        $inv = Inventory::from('inventories as inv')->leftJoin('kartu_inventories as ki', 'ki.inventory_id', '=', 'inv.id')
            ->where('ki.book_journal_id', session('book_journal_id'))
            ->whereYear('ki.date', $year)
            ->select(
                'inv.id',
                'inv.name',
                'inv.type_aset',
                'inv.keterangan_qty_unit',
                'inv.date',
                'inv.nilai_perolehan',
                'inv.periode',
                DB::raw('SUM( case when ki.amount>0 then ki.amount else 0 end) as total_pembelian'),
                DB::raw('SUM( case when ki.amount<0 then ki.amount else 0 end) as total_penyusutan'),
                DB::raw('date_format(ki.date, "%Y-%m") as bulan_susut'),
            )
            ->groupBy(DB::raw('date_format(ki.date,"%Y-%m")'), 'inv.id')->get()->groupBy('type_aset')
            ->map(function ($val) {
                return collect($val)->groupBy('id')->map(function ($theval) {
                    return [
                        'id' => $theval[0]->id,
                        'name' => $theval[0]->name,
                        'type_aset' => $theval[0]->type_aset,
                        'keterangan_qty_unit' => $theval[0]->keterangan_qty_unit,
                        'date' => $theval[0]->date,
                        'nilai_perolehan' => $theval[0]->nilai_perolehan,
                        'total_pembelian' => $theval[0]->total_pembelian,
                        'periode' => $theval[0]->periode,
                        'total_penyusutan' => $theval[0]->total_penyusutan,
                        'penyusutan' => collect($theval)->keyBy('bulan_susut')
                    ];
                });
            });
        $saldoBukuAkhir = KartuInventory::join('inventories as inv', 'inv.id', '=', 'kartu_inventories.inventory_id')->whereIn('kartu_inventories.id', function ($q) use ($year) {
            $q->from('kartu_inventories as ki')->where('ki.book_journal_id', session('book_journal_id'))
                ->whereYear('ki.date', $year)->select(DB::raw('max(id) as maxid'))->groupBy('inventory_id');
        })->select('inventory_id', 'nilai_buku', 'inv.name')->get()->keyBy('inventory_id');

        return [
            'status' => 1,
            'msg' => $inv,
            'saldo_buku_akhir' => $saldoBukuAkhir
        ];
    }
    public function getMutasiMasuk()
    {
        $year = getInput('year') ? getInput('year') : date('Y');
        $kartu = KartuInventory::from('kartu_inventories as ki')->join('inventories as inv', 'inv.id', '=', 'ki.inventory_id')
            ->whereYear('ki.date', $year)->where('ki.amount', '>', 0)
            ->select('ki.*', 'inv.name', 'inv.type_aset')->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }
    public function getMutasiKeluar()
    {
        $year = getInput('year') ? getInput('year') : date('Y');
        $kartu = KartuInventory::from('kartu_inventories as ki')->join('inventories as inv', 'inv.id', '=', 'ki.inventory_id')
            ->whereYear('ki.date', $year)->where('ki.amount', '<', 0)
            ->select('ki.*', 'inv.name', 'inv.type_aset')->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }
}
