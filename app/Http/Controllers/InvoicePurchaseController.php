<?php

namespace App\Http\Controllers;

use App\Models\ChartAccount;
use App\Models\DetailKartuInvoice;
use App\Models\InvoicePack;
use App\Models\InvoicePurchaseDetail;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Models\InvoicePurchase;
use App\Models\Journal;
use App\Models\KartuHutang;
use App\Models\KartuStock;
use App\Models\Toko;
use Dotenv\Exception\ValidationException;

class InvoicePurchaseController extends Controller
{


    public function showPurchase()
    {
        $month = getInput('month') ? toDigit(getInput('month'), 2) : date('m');
        $year = getInput('year') ? getInput('year') : date('Y');
        $parent = [];

        $invoices = InvoicePurchaseDetail::whereMonth('created_at', $month)->whereYear('created_at', $year)->with(['parent', 'stock', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('invoice_pack_number');
        $invPack = InvoicePack::whereMonth('created_at', $month)->whereYear('created_at', $year)->where('reference_model', InvoicePurchaseDetail::class)
            ->select('is_final', 'is_mark', 'total_price')->get();
        $totalInvoice = collect($invPack)->sum('total_price');
        $totalInvoiceFinal = collect($invPack)->where('is_final', 1)->sum('total_price');
        $totalInvoiceMark = collect($invPack)->where('is_mark', 1)->sum('total_price');

        return view('invoice.invoice-purchase', compact('invoices', 'month', 'year', 'totalInvoice', 'totalInvoiceFinal', 'totalInvoiceMark', 'parent'));
    }



    public function editInvoicePurchase($invoiceNumber)
    {



        $data = InvoicePack::where('invoice_number', $invoiceNumber)->first();

        $details = InvoicePurchaseDetail::with('stock')
            ->where('invoice_pack_id', $data->id)
            ->get();




        $data['details'] = $details;


        $view = view('invoice.modal._edit-purchase', [
            'invoiceNumber' => $invoiceNumber,
            'data' => $data,
        ]);
        $view->invoiceNumber = $invoiceNumber;
        $view->data = $data;

        return $view;
    }

    public function updateInvoicePurchase(Request $request)
    {

        try {

            DB::beginTransaction();
            $invoiceNumber = $request->input('original_invoice_number');
            $newInvoiceNumber = $request->input('new_invoice_number');
            $invoicePack = InvoicePack::where('invoice_number', $invoiceNumber)->first();
            $detailIDs = $request->input('detail_id');
            $date = $request->input('date');
            $allInv = [];
            foreach ($detailIDs as $i => $detailId) {
                $invDetail = InvoicePurchaseDetail::find($detailId);
                if (!$invDetail) {
                    throw new ValidationException("Detail with ID {$detailId} not found.");
                }
                $data = [
                    'invoice_pack_number' => $newInvoiceNumber,
                    'quantity' => format_db($request->input('quantity')[$i]),
                    'price' => format_db($request->input('price')[$i]),
                    'discount' => format_db($request->input('discount')[$i]) ?? 0,
                    'total_price' => format_db($request->input('total_price')[$i]) ?? 0,
                    'custom_stock_name' => $request->input('custom_stock_name')[$i] ?? $invDetail->stock->name,
                    'created_at' => $date
                ];
                $invDetail->update($data);
                $allInv[] = $invDetail;
            }
            $invoicePack->update([
                'invoice_number' => $newInvoiceNumber,
                'total_price' => collect($allInv)->sum('total_price'),
                'created_at' => $date
            ]);
            DB::commit();
            return ['status' => 1, 'msg' => $invoicePack, 'details' => $allInv];
        } catch (ValidationException $e) {
            DB::rollBack();
            return ['status' => 0, 'msg' => getErrorValidation($e)];
        } catch (Throwable $e) {
            DB::rollBack();
            return ['status' => 0, 'msg' => $e->getMessage(), 'trace' => $e->getTrace()];
        }
    }

    public function createMutations(Request $request)
    {

        $coaDebet = $request->input('code_group_debet');
        $coaKredit = $request->input('code_group_kredit');
        $toko = Toko::first();

        $invoicePackID = $request->input('invoice_pack_id');
        $invoicePackNumber = $request->input('invoice_pack_number');
        $date = $request->input('date');
        $nilaiMutasi = format_db($request->input('nilai_mutasi'));
        $quantity = $request->input('quantity');
        $unit = $request->input('unit');
        $isBackdate = KartuHutang::isBackdate($date);
        $stockId = $request->input('stock_id');
        $invoicePack = InvoicePack::find($invoicePackID);
        if (!$invoicePack) {
            return ['status' => 0, 'msg' => 'Invoice tidak ditemukan'];
        }
        $chartPersediaan = ChartAccount::where('code_group', $coaDebet)->first();
        $chartHutangKas = ChartAccount::where('code_group', $coaKredit)->first();
        if (!$chartPersediaan || !$chartHutangKas) {
            return ['status' => 0, 'msg' => 'Chart account tidak ditemukan'];
        }
        //buat kartu stock
        //dari sini apa yang sudah dibuat harus disimpan dulu. trus kalo gagal ditengah jalan kita rollback atau delete
        DB::beginTransaction();
        try {
            $ks = [];
            if ($coaDebet > 140000 && $coaDebet < 150000) {
                $kartuStock = KartuStock::mutationStore(new Request([
                    'stock_id' => $stockId,
                    'mutasi_quantity' => $quantity,
                    'unit' => $unit,
                    'flow' => 0,
                    'code_group' => $coaDebet,
                    'invoice_pack_number' => $invoicePackNumber,
                    'invoice_pack_id' => $invoicePackID,
                    'is_custom_rupiah' => 1,
                    'mutasi_rupiah_total' => $nilaiMutasi,
                    'date' => $date
                ]), false);
                if ($kartuStock['status'] == 0) {
                    throw new \Exception($kartuStock['msg']);
                }
                $ks[] = $kartuStock['msg'];
            }
            //oke sampek sini chat dan invoice sudah valid
            if ($coaKredit > 200000) {
                //brati hutang, buat kartu hutang ya lur
                $kartu = KartuHutang::createMutation(new Request([
                    'invoice_pack_number' => $invoicePackNumber,
                    'invoice_pack_id' => $invoicePackID,
                    'amount_mutasi' => $nilaiMutasi,
                    'person_id' => $invoicePack->person_id,
                    'person_type' => $invoicePack->person_type,
                    'code_group' => $coaKredit,
                    'lawan_code_group' => $coaDebet,
                    'is_otomatis_jurnal' => 1,
                    'date' => $date,
                    'toko_id' => $toko->id,
                    'description' => 'inv pembelian ' . $invoicePack->person->name . ' nomer ' . $invoicePack->invoice_number,
                ]), false);
                if ($kartu['status'] == 0) {
                    throw new \Exception($kartu['msg']);
                }
                $kartuHutang = $kartu['msg'];
                $journalNumber = $kartuHutang->journal_number;
            } else {
                //nah disini ini kalau ternyata lansung dibayar pakai kas lur

                $debets = [
                    [
                        'code_group' => $coaDebet,
                        'description' => 'pembelian ' . $invoicePack->person->name . ' nomer ' . $invoicePack->invoice_number,
                        'amount' => $invoicePack->total_price,
                        'reference_id' => null,
                        'reference_type' => null,
                        'toko_id' => $toko->id

                    ],
                ];
                $kredits = [
                    [
                        'code_group' => $coaKredit,
                        'description' => 'pembelian nomer ' . $invoicePack->invoice_number,
                        'amount' => $invoicePack->total_price,
                        'reference_id' => null,
                        'reference_type' => null,
                        'toko_id' => $toko->id
                    ],
                ];
                $st = JournalController::createBaseJournal(new Request([
                    'kredits' => $kredits,
                    'debets' => $debets,
                    'type' => 'purchasing',
                    'date' => $date,
                    'is_auto_generated' => 1,
                    'is_backdate' => $isBackdate,
                    'title' => 'create mutation purchase',
                    'url_try_again' => null

                ]), false);
                if ($st['status'] == 0) {
                    throw new \Exception($st['msg']);
                }
                $journalNumber = $st['journal_number'];
            }
            if ($coaDebet > 140000 && $coaDebet < 150000) {
                $journalPersediaan = Journal::where('journal_number', $journalNumber)
                    ->where('code_group', $coaDebet)->first();

                foreach ($ks as $k) {
                    $kartu = KartuStock::find($k->id);
                    $kartu->journal_id = $journalPersediaan->id;
                    $kartu->journal_number = $journalPersediaan->journal_number;
                    $kartu->save();
                    $kartu->createDetailKartuInvoice();
                }
            }

            DB::commit();
            return [
                'status' => 1,
                'msg' => 'Berhasil membuat claim pembelian',
                'kartu_hutang' => $kartuHutang ?? null,
                'kartu_stock' => $ks,

            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        } catch (Throwable $th) {
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        } finally {
        }
    }

    public function store(Request $request)
    {


        $request->validate([
            'invoice_pack_number' => 'required|string|max:255',
            'supplier_id' => 'required|integer',
            'stock_id' => 'required|array',
            'stock_id.*' => 'required|integer',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric',
            'price_unit' => 'required|array',
            'price_unit.*' => 'required|numeric',
            'unit' => 'required|array',
            'unit.*' => 'required|string',
            'total_price' => 'required|array',
            'total_price.*' => 'required|string',
            'discount' => 'nullable|array',
            'discount.*' => 'nullable|numeric',
        ]);

        $invoice_pack_number = $request->invoice_pack_number;
        $grouped = [];
        $date = $request->input('date');
        foreach ($request->stock_id as $i => $stockId) {
            $thestock = Stock::find($stockId);
            $grouped[] = [
                'invoice_pack_number' => $invoice_pack_number,
                'stock_id' => $stockId,
                'quantity' => $request->quantity[$i],
                'unit' => $request->unit[$i],
                'price' => $request->price_unit[$i],
                'discount' => $request->discount[$i] ?? 0,
                'supplier_id' => $request->supplier_id,
                'book_journal_id' => bookID(),
                'total_price' => format_db($request->total_price[$i]) ?? 0,
                'custom_stock_name' => $request->custom_stock_name[$i] ?? $thestock->name,
                'created_at' => $date ?? now()
            ];
        }

        DB::beginTransaction();
        try {

            //create pack ya
            $invoicePack = InvoicePack::create([

                'invoice_number' => $invoice_pack_number,
                'book_journal_id' => bookID(),
                'person_id' => $request->supplier_id,
                'person_type' => 'App\Models\Supplier',
                'reference_model' => 'App\Models\InvoicePurchaseDetail',
                'invoice_date' => now(),
                'total_price' => collect($grouped)->sum('total_price'),
                'status' => 'draft',
                'created_at' => $date ?? now()
            ]);


            foreach ($grouped as $data) {
                $data['invoice_pack_id'] = $invoicePack->id;
                InvoicePurchaseDetail::create($data);
            }
            DB::commit();
        } catch (Throwable $e) {

            DB::rollBack();
            return ['status' => 0, 'msg' => $e->getMessage(), 'trace' => $e->getTrace()];
        }

        return ['status' => 1, 'msg' => 'Data berhasil disimpan'];
    }


    public function destroy($id)
    {
        $invoicePurchase = InvoicePack::find($id);
        $details = InvoicePurchaseDetail::where('invoice_pack_id', $id)->get();
        if (!$invoicePurchase) {
            return ['status' => 0, 'msg' => 'Invoice tidak ditemukan'];
        }
        if ($invoicePurchase->is_final) {
            return ['status' => 0, 'msg' => 'Invoice sudah final, tidak bisa dihapus'];
        }
        DB::beginTransaction();
        try {
            foreach ($details as $detail) {
                $detail->delete();
            }
            $invoicePurchase->delete();
            DB::commit();
            return ['status' => 1, 'msg' => 'Invoice berhasil dihapus'];
        } catch (Throwable $e) {
            DB::rollBack();
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }
}
