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

        // return $request->all();
        DB::beginTransaction();
        $inv = null;
        $st = null;
        try {
            $request['book_journal_id'] = bookID();
            $request['nilai_perolehan'] = format_db($request['nilai_perolehan']);
            $validate = $request->validate([
                'name' => 'required|string',
                'description' => 'string',
                'keterangan_qty_unit' => 'string',
                'date' => 'required|date',
                'nilai_perolehan' => 'required|numeric',
                'periode' => 'required|integer',
                'book_journal_id' => 'required|integer',
                'type_aset' => 'required|string',
                'code_group' => 'required|integer',
                'lawan_code_group' => 'required|integer',
                'toko_id' => 'required',
            ]);


            $inv = Inventory::create($request->all());
            $inv->refresh();

            if ($inv == null) {
                throw new \Exception('Gagal menyimpan data');
            }
            $st = KartuInventory::createKartu(new Request([
                'inventory_id' => $inv->id,
                'date' => $request['date'],
                'description' => $request['description'],
                'toko_id' => $request['toko_id'],
                'amount' => $request['nilai_perolehan'], // ini pake format indonesia
                'type_mutasi' => 'pembelian',
                'code_group' => $request['code_group'],
                'lawan_code_group' => $request['lawan_code_group'],
                'is_otomatis_jurnal' => $request['is_otomatis_jurnal'] ?? 0,
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
        }
        DB::commit();
        return [
            'status' => 1,
            'msg' => $st['msg']
        ];
    }

    public function getItem()
    {
        $inv = Inventory::select('id', DB::raw('name as text'))->get();
        return [
            'results' => $inv
        ];
    }

    public function kartuMutasi($id)
    {
        $inventory = Inventory::find($id);
        $view = view('kartu.modal._kartu-mutasi-inventory');
        $view->inventory = $inventory;
        $view->data = KartuInventory::where('inventory_id', $id)->orderBy('index_date', 'asc')->get();
        return $view;
    }

    public static function getSummary($year = null)
    {
        if (!$year)
            $year = getInput('year') ? getInput('year') : date('Y');
        $indexLastYear = createCarbon($year . '-01-01')->endOfYear()->format('ymdHis000');
        $indexFirstYear = createCarbon($year . '-01-01')->startOfYear()->format('ymdHis000');
        $inventoryAktif = KartuInventory::whereIn('index_date', function ($q) use ($indexFirstYear) {
            $q->select(DB::raw('max(index_date)'))->from('kartu_inventories')->where('index_date', '<', $indexFirstYear)->groupBy('inventory_id');
        })->where('nilai_buku', '>', 0)->select('inventory_id')->pluck('inventory_id')->toArray();
        $saldoBukuAkhir = KartuInventory::join('inventories as inv', 'inv.id', '=', 'kartu_inventories.inventory_id')->whereIn('kartu_inventories.index_date', function ($q) use ($indexLastYear) {
            $q->from('kartu_inventories as ki')->where('ki.book_journal_id', bookID())
                ->where('index_date', '<', $indexLastYear)->select(DB::raw('max(index_date) as maxid'))->groupBy('inventory_id');
        })->select('inventory_id', 'nilai_buku', 'inv.name')->get()->keyBy('inventory_id');
        $idakhir = collect($saldoBukuAkhir)->keys()->toArray();
        $inventoryAktif = array_unique(array_merge($inventoryAktif, $idakhir));
        $inv = Inventory::from('inventories as inv')->whereIn('inv.id', $inventoryAktif)->leftJoin('kartu_inventories as ki', 'ki.inventory_id', '=', 'inv.id')
            ->where('index_date', '<', $indexLastYear)
            ->where('ki.book_journal_id', bookID())

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
                        'total_penyusutan' => collect($theval)->sum('total_penyusutan'),
                        'penyusutan' => collect($theval)->pluck('total_penyusutan', 'bulan_susut')
                    ];
                });
            });


        return [
            'status' => 1,
            'msg' => $inv,
            'saldo_buku_akhir' => $saldoBukuAkhir,
            'year' => $year,
            'index_first_year' => $indexFirstYear,
            'index_last_year' => $indexLastYear
        ];
    }
    public function getMutasiMasuk()
    {

        $year = getInput('year') ? getInput('year') : date('Y');
        $indexLastYear = createCarbon($year . '-01-01')->endOfYear()->format('ymdHis000');
        $indexFirstYear = createCarbon($year . '-01-01')->startOfYear()->format('ymdHis000');

        $kartu = KartuInventory::from('kartu_inventories as ki')->join('inventories as inv', 'inv.id', '=', 'ki.inventory_id')
            ->where('ki.index_date', '<', $indexLastYear)->where('ki.index_date', '>=', $indexFirstYear)->where('ki.amount', '>', 0)
            ->select('ki.*', 'inv.name', 'inv.type_aset')->orderBy('ki.index_date','asc')->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }
    public function getMutasiKeluar()
    {
        $year = getInput('year') ? getInput('year') : date('Y');
        $indexLastYear = createCarbon($year . '-01-01')->endOfYear()->format('ymdHis000');
        $indexFirstYear = createCarbon($year . '-01-01')->startOfYear()->format('ymdHis000');

        $kartu = KartuInventory::from('kartu_inventories as ki')->join('inventories as inv', 'inv.id', '=', 'ki.inventory_id')
            ->where('ki.index_date', '<', $indexLastYear)->where('ki.index_date', '>=', $indexFirstYear)->where('ki.amount', '<', 0)
            ->select('ki.*', 'inv.name', 'inv.type_aset')->orderBy('ki.index_date','asc')->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }
}
