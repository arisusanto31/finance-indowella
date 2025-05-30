<?php

namespace App\Http\Controllers;

use App\Jobs\ImportSaldoNLJob;
use App\Models\Journal;
use App\Models\KartuDPSales;
use App\Models\KartuHutang;
use App\Models\KartuInventory;
use App\Models\KartuPiutang;
use App\Models\KartuPrepaidExpense;
use App\Models\KartuStock;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\TaskImportDetail;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        return redirect('admin/dashboard');
    }
    public function dashboard()
    {
        return view('dashboard');
    }
    public function random()
    {
        $view = view('main.random');
        return $view;
    }

    public function inspectJurnal()
    {
        $journalKartuStock = Journal::where('reference_model', 'App\\Models\\KartuStock')
            ->leftJoin('kartu_stocks', 'kartu_stocks.journal_id', '=', 'journals.id')
            ->whereNull('kartu_stocks.id')
            ->count();
        $journalKartuHutang = Journal::where('reference_model', 'App\\Models\\KartuHutang')
            ->leftJoin('kartu_hutangs', 'kartu_hutangs.journal_id', '=', 'journals.id')
            ->whereNull('kartu_hutangs.id')
            ->count();
        $journalKartuPiutang = Journal::where('reference_model', 'App\\Models\\KartuPiutang')
            ->leftJoin('kartu_piutangs', 'kartu_piutangs.journal_id', '=', 'journals.id')
            ->whereNull('kartu_piutangs.id')
            ->count();
        $KartuPrepaid = Journal::where('reference_model', 'App\\Models\\KartuPrepaidExpense')
            ->leftJoin('kartu_prepaid_expenses', 'kartu_prepaid_expenses.journal_id', '=', 'journals.id')
            ->whereNull('kartu_prepaid_expenses.id')
            ->count();
        $kartuInventory = Journal::where('reference_model', 'App\\Models\\KartuInventory')
            ->leftJoin('kartu_inventories', 'kartu_inventories.journal_id', '=', 'journals.id')
            ->whereNull('kartu_inventories.id')
            ->count();
        $problemJournal = $journalKartuStock + $journalKartuHutang + $journalKartuPiutang + $KartuPrepaid + $kartuInventory;

        $problemKartuStock = KartuStock::where('journal_id', null)
            ->count();
        $problemKartuHutang = KartuHutang::where('journal_id', null)
            ->count();
        $problemKartuPiutang = KartuPiutang::where('journal_id', null)
            ->count();
        $problemKartuPrepaid = KartuPrepaidExpense::where('journal_id', null)
            ->count();
        $problemKartuInventory = KartuInventory::where('journal_id', null)
            ->count();


        return [
            'status' => 1,
            'problem_journal' => $problemJournal,
            'problem_kartu_stock' => $problemKartuStock,
            'problem_kartu_hutang' => $problemKartuHutang,
            'problem_kartu_piutang' => $problemKartuPiutang,
            'problem_kartu_prepaid' => $problemKartuPrepaid,
            'problem_kartu_inventory' => $problemKartuInventory,
            'total' => $problemJournal + $problemKartuStock + $problemKartuHutang + $problemKartuPiutang + $problemKartuPrepaid + $problemKartuInventory

        ];
    }

    public function areaDeveloper()
    {
        if (getInput('type') == "pattern") {
            return detectFormat(getInput('nilai'));
        }
        if (getInput('type') == 'format_db') {
            return format_db(getInput('nilai'));
        }

        if (getInput('type') == 'repair-kartu-dp') {
            $kartuDP = KartuDPSales::where('index_date', 0)->get();
            foreach ($kartuDP as $dp) {
                $so = SalesOrder::where('sales_order_number', $dp->sales_order_number)->first();
                $dp->index_date = KartuDPSales::getNextIndexDate($so->created_at);
                $dp->index_date_group = createCarbon($so->created_at)->format('ymdHis');
                $dp->save();
            }
            return $kartuDP;
        }
        if (getInput('type') == 'recalculate-kartu') {
            $kartu = getInput('model')::find(getInput('id'));
            $kartu->recalculateSaldo();
            return $kartu;
        }
        if (getInput('type') == 'recalculate-journal') {
            $journal = Journal::find(getInput('id'));
            $journal->recalculateJournal();
            return $journal;
        }

        if (getInput('type') == 'repair-price') {
            $sodetail = SalesOrderDetail::where('sales_order_number', getInput('number'))
                ->get();
            foreach ($sodetail as $detail) {
                $detail->pricejadi = ($detail->total_price + $detail->discount) / $detail->qtyjadi;
                $detail->save();
            }
            return $sodetail;
        }


        if (getInput('type') == 'repair-task') {

            $task = TaskImportDetail::where('type', 'kartu_stock');
            if (getInput('take')) {
                $task = $task->take(getInput('take'));
            }
            $repair = [];
            $task = $task->get();
            foreach ($task as $t) {
                $payload = json_decode($t->payload, true);
                $qty = $payload['unit'];
                $payload['unit'] = $payload['quantity'];
                $payload['quantity'] = $qty;
                $t->payload = json_encode($payload);
                $t->save();
                $repair[] = $t;
            }
            return $repair;
        }
    }
}
