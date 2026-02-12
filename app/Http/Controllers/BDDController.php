<?php

namespace App\Http\Controllers;

use App\Models\KartuPrepaidExpense;
use App\Models\PrepaidExpense;
use App\Models\TaskImportDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class BDDController extends Controller
{

    public function index()
    {
        $view = view('daftar.bdd');
        $view->year = getInput('year') ? getInput('year') : date('Y');
        return $view;
    }


    public function createPrepaid()
    {
        return view('daftar.modal._create_prepaid');
    }
    public function createKartuPrepaid()
    {
        return view('daftar.modal._kartu_prepaid');
    }

    public function storePrepaid(Request $request)
    {


        DB::beginTransaction();
        $inv = null;
        $st = null;
        try {
            $request['book_journal_id'] = bookID();
            $request['nilai_perolehan'] = format_db($request['nilai_perolehan']);
            $validate = $request->validate([
                'name' => 'required|string',
                'keterangan_qty_unit' => 'string',
                'date' => 'required|date',
                'nilai_perolehan' => 'required|numeric',
                'periode' => 'required|integer',
                'book_journal_id' => 'required|integer',
                'type_bdd' => 'required|string',
                'code_group' => 'required|numeric',
                'lawan_code_group' => 'required|numeric',
            ]);


            $inv = PrepaidExpense::create($request->all());
            $inv->refresh();
            if ($inv == null) {
                throw new \Exception('Gagal menyimpan data');
            }
            $st = KartuPrepaidExpense::createKartu(new Request([
                'description' => $request['description'] ?? '',

                'prepaid_expense_id' => $inv->id,
                'date' => $request['date'],
                'amount' => $request['nilai_perolehan'], // ini pake format indonesia
                'type_mutasi' => 'pembayaran',
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

    public function storeKartuPrepaid(Request $request)
    {
        try {
            $st = KartuPrepaidExpense::createKartu($request);
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
        $inv = PrepaidExpense::select('id', DB::raw('name as text'))->get();
        return [
            'results' => $inv
        ];
    }

    public static function getSummary($year = null)
    {
        if (!$year)
            $year = getInput('year') ? getInput('year') : date('Y');
        $inv = PrepaidExpense::from('prepaid_expenses as inv')->leftJoin('kartu_prepaid_expenses as ki', 'ki.prepaid_expense_id', '=', 'inv.id')
            ->where('ki.book_journal_id', bookID())
            ->whereYear('ki.date', $year)
            ->select(
                'inv.id',
                'inv.name',
                'inv.type_bdd',
                'inv.date',
                'inv.nilai_perolehan',
                'inv.periode',
                DB::raw('SUM( case when ki.amount>0 then ki.amount else 0 end) as total_pembelian'),
                DB::raw('SUM( case when ki.amount<0 then ki.amount else 0 end) as total_penyusutan'),
                DB::raw('date_format(ki.date, "%Y-%m") as bulan_susut'),
            )
            ->groupBy(DB::raw('date_format(ki.date,"%Y-%m")'), 'inv.id')->get()->groupBy('type_bdd')
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
                        'penyusutan' => collect($theval)->pluck('total_penyusutan', 'bulan_susut')
                    ];
                });
            });
        $saldoBukuAkhir = KartuPrepaidExpense::join('prepaid_expenses as inv', 'inv.id', '=', 'kartu_prepaid_expenses.prepaid_expense_id')->whereIn('kartu_prepaid_expenses.id', function ($q) use ($year) {
            $q->from('kartu_prepaid_expenses as ki')->where('ki.book_journal_id', bookID())
                ->whereYear('ki.date', $year)->select(DB::raw('max(id) as maxid'))->groupBy('prepaid_expense_id');
        })->select('prepaid_expense_id', 'nilai_buku', 'inv.name')->get()->keyBy('prepaid_expense_id');

        return [
            'status' => 1,
            'msg' => $inv,
            'saldo_buku_akhir' => $saldoBukuAkhir,
            'year' => $year
        ];
    }
    public function getMutasiMasuk()
    {
        $year = getInput('year') ? getInput('year') : date('Y');
        $kartu = KartuPrepaidExpense::from('kartu_prepaid_expenses as ki')->join('prepaid_expenses as inv', 'inv.id', '=', 'ki.prepaid_expense_id')
            ->whereYear('ki.date', $year)->where('ki.amount', '>', 0)
            ->select('ki.*', 'inv.name')->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }
    public function getMutasiKeluar()
    {
        $year = getInput('year') ? getInput('year') : date('Y');
        $kartu = KartuPrepaidExpense::from('kartu_prepaid_expenses as ki')->join('prepaid_expenses as inv', 'inv.id', '=', 'ki.prepaid_expense_id')
            ->whereYear('ki.date', $year)->where('ki.amount', '<', 0)
            ->select('ki.*', 'inv.name')->get();
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }

    public static function processTaskImport($id)
    {
        try {
            DB::beginTransaction();
            $task = TaskImportDetail::find($id);
            if (!$task) {
                throw new \Exception('Task import tidak ditemukan');
            }
            $payload = $task->payload ? json_decode($task->payload, true) : [];
            $nameInv = $payload['name'] ?? '';
            if (!$nameInv || $nameInv == '') {
                throw new \Exception('Nama inventaris tidak boleh kosong');
            }
            $inv = PrepaidExpense::where('name', $nameInv)->first();
            if (!$inv) {
                $inv = new PrepaidExpense;
                $inv->name = $nameInv;
            }
            $inv->type_bdd = 'Biaya Sewa';
            $inv->date = $payload['date'];
            $inv->nilai_perolehan = $payload['nilai_perolehan'];
            $inv->periode = $payload['periode'];
            $inv->book_journal_id = bookID();
            $inv->toko_id = $payload['toko_id'] ?? 1;
            $inv->save();


            $kartu = KartuPrepaidExpense::where('prepaid_expense_id', $inv->id)->where('tag', 'init_import' . $payload['date'])->first();
            if (!$kartu) {
                $kartu = new KartuPrepaidExpense;
                $kartu->tag = 'init_import' . $payload['date'];
                $kartu->prepaid_expense_id = $inv->id;
            }
            $created = createCarbon($payload['date'] . ' 08:00:00');
            $kartu->index_date = KartuPrepaidExpense::getNextIndexDate($created);
            $kartu->index_date_group = $created->format('ymdHis');
            $kartu->type_mutasi = 'init';
            $kartu->amount = 0;
            $kartu->date = $payload['date'];
            $kartu->book_journal_id = bookID();
            $kartu->description = 'saldo awal import inventaris ' . $inv->name;
            $kartu->toko_id = $inv->toko_id;
            $kartu->nilai_buku = floatval($payload['nilai_buku']);
            $kartu->save();

            $task->status = 'success';
            $task->error_message = "";
            $task->finished_at = now();
            $task->save();
            DB::commit();
            return [
                'status' => 1,
                'msg' => $kartu,
                'task' => $task
            ];
        } catch (Throwable $th) {
            DB::rollBack();
            $task = TaskImportDetail::find($id);
            if ($task) {
                $task->status = 'failed';
                $task->error_message = $th->getMessage();
                $task->save();
            }
            return [
                'status' => 0,
                'task' => $task,
                'msg' => $th->getMessage()
            ];
        }
    }
}
