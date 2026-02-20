<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\KartuHutang;
use App\Models\Supplier;
use App\Models\TaskImportDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuHutangController extends Controller
{

    public function index()
    {

        $view = view('kartu.kartu-hutang');
        $view->month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
        $view->year = getInput('year') ? getInput('year') : Date('Y');

        return $view;
    }

    public function createMutation(Request $request)
    {
        return KartuHutang::createMutation($request);
    }

    public function createPelunasan(Request $request)
    {
        return KartuHutang::createPelunasan($request);
    }

    public function getSummary()
    {
        $month = getInput('month') ?? Date('m');
        $year = getInput('year') ?? Date('Y');

        return KartuHutang::getSummary($year, $month, 'factur_supplier_number');
    }

    function getMutasiMasuk()
    {
        $month = getInput('month') ?? Date('m');
        $year = getInput('year') ?? Date('Y');
        return KartuHutang::getMutasi($year, $month, 'mutasi');
    }

    function getMutasiKeluar()
    {
        $month = getInput('month') ?? Date('m');
        $year = getInput('year') ?? Date('Y');
        return KartuHutang::getMutasi($year, $month, 'pelunasan');
    }

    public function showDetail($nomer)
    {
        $view = view('kartu.modal._kartu-mutasi-hutang');
        $view->factur = $nomer;
        $kh = KartuHutang::where('factur_supplier_number', $nomer)->orderBy('created_at', 'desc')->first();
        $view->person = $kh->person;
        $data = KartuHutang::where('factur_supplier_number', $nomer)->get();
        $view->data = $data;
        return $view;
    }

    public function searchLinkJournal()
    {
        $journals = Journal::where('reference_model', KartuHutang::class)
            ->whereNull('verified_by')->with(['codeGroupData:code_group,name', 'codeGroupLawanData:code_group,name'])->get();
        return [
            'status' => 1,
            'msg' => $journals
        ];
    }

    public function refresh($id)
    {
        $kartu = KartuHutang::find($id);
        $detail = $kartu->createDetailKartuInvoice();
        if ($detail['status'] == 0) {
            return $detail;
        }
        $kartu->refreshSaldo();
        $kartu->recalculateSaldo();
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
            if ($task->type != 'kartu_hutang') {
                return;
            }
            $payload = $task->payload ? json_decode($task->payload, true) : [];
            if ($payload['supplier_name'] == '') {
                return;
            }
            $supplier = DB::table('suppliers')->where('name', $payload['supplier_name'])->first();
            if (!$supplier) {
                $supplier = new Supplier();
                $supplier->name = $payload['supplier_name'];
                $supplier->book_journal_id = book()->id;
                $supplier->save();
            }
            $kartuHutang = KartuHutang::where('factur_supplier_number', $payload['factur_supplier_number'])->where('tag', 'init_import' . $payload['request_date'])->first();
            if (!$kartuHutang) {
                $kartuHutang = new KartuHutang;
                $kartuHutang->tag = 'init_import' . $payload['request_date'];
                $kartuHutang->factur_supplier_number = $payload['factur_supplier_number'];
            }
            $created = createCarbon($payload['request_date'] . ' 00:00:00');
            $kartuHutang->index_date = KartuHutang::getNextIndexDate($created);
            $kartuHutang->index_date_group = $created->format('ymdHis');

            $kartuHutang->type = 'init';
            $kartuHutang->book_journal_id = book()->id;
            $kartuHutang->factur_supplier_number = $payload['factur_supplier_number'];
            $kartuHutang->description = 'saldo awal import kartu hutang';
            $kartuHutang->amount_kredit = 0;
            $kartuHutang->amount_debet = 0;
            $kartuHutang->amount_saldo_factur = floatval($payload['saldo_akhir']);
            $lastSaldoPerson = KartuHutang::where('person_id', $kartuHutang->person_id)->where('person_type', $kartuHutang->person_type)
                ->where('index_date', '<', $kartuHutang->index_date)->orderBy('index_date', 'desc')->first();
            $saldoPerson = $lastSaldoPerson ? $lastSaldoPerson->amount_saldo_person : 0;
            $kartuHutang->amount_saldo_person = $saldoPerson + $kartuHutang->amount_saldo_factur;
            $kartuHutang->invoice_date = $payload['date'];
            $kartuHutang->person_id = $supplier->id;
            $kartuHutang->person_type = Supplier::class;
            $kartuHutang->factur_tax_number = $payload['factur_tax_number'] ?? null;
            $kartuHutang->save();

            $task->status = 'success';
            $task->error_message = "";
            $task->finished_at = now();
            $task->save();
            DB::commit();

            return [
                'status' => 1,
                'msg' => $kartuHutang,
                'task'=> $task
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            info('error import kartu hutang : ' . $e->getMessage());
            $task = TaskImportDetail::find($id);
            $task->status = 'error';
            $task->error_message = $e->getMessage();
            $task->save();
            return [
                'status' => 0,
                'task'=> $task,
                'msg' => 'Error import kartu hutang : ' . $e->getMessage()
            ];
        }
    }
}
