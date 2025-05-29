<?php

namespace App\Http\Controllers;

use App\Models\ChartAccount;
use App\Models\DetailKartuInvoice;
use App\Models\InvoicePack;
use App\Models\InvoicePurchaseDetail;
use App\Models\InvoiceSaleDetail;
use App\Models\Journal;
use App\Models\KartuHutang;
use App\Models\KartuPiutang;
use App\Models\KartuStock;
use App\Models\SalesOrder;
use App\Services\LockManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class InvoicePackController extends Controller
{

    public function show($id)
    {
        $invoice = InvoicePack::find($id);
        if (!$invoice) {
            dd('Invoice tidak ditemukan');
        }

        return view('invoice.show', compact('invoice'));
    }
    public function showDetail($number)
    {
        $data = InvoicePack::where('invoice_number', $number)->first();
        $invdetails = $data->reference_model::with('stock')->where('invoice_pack_number', $number)->get();
        $data['details'] = $invdetails;
        $data['kartus'] = $data->getAllKartu();

        $view = view('invoice.modal._invoice-detail');
        $view->data = $data;
        return $view;
    }

    public function createClaimPenjualan(Request $request)
    {
        $lockManager = new LockManager();
        $coaPenjualan = $request->input('coa_penjualan');
        $coaBeban = 601000;
        $coaPersediaan = $request->input('coa_persediaan');
        $coaPiutangKas = $request->input('coa_piutang_kas');

        $invoicePackID = $request->input('invoice_pack_id');
        $invoicePack = InvoicePack::find($invoicePackID);
        if (!$invoicePack) {
            return ['status' => 0, 'msg' => 'Invoice tidak ditemukan'];
        }
        $salesOrder = SalesOrder::where('sales_order_number', $invoicePack->sales_order_number)->first();

        $chartPenjualan = ChartAccount::where('code_group', $coaPenjualan)->first();
        $chartBeban = ChartAccount::where('code_group', $coaBeban)->first();
        $chartPersediaan = ChartAccount::where('code_group', $coaPersediaan)->first();
        $chartPiutangKas = ChartAccount::where('code_group', $coaPiutangKas)->first();
        if (!$chartPersediaan || !$chartPiutangKas || !$chartPenjualan || !$chartBeban) {
            return ['status' => 0, 'msg' => 'Chart account tidak ditemukan'];
        }

        //buat kartu stock
        //dari sini apa yang sudah dibuat harus disimpan dulu. trus kalo gagal ditengah jalan kita rollback atau delete
        DB::beginTransaction();
        try {
            $details = $invoicePack->invoiceDetails();
            if (count($details) == 0) {
                throw new \Exception('Tidak ada detail invoice');
            }
            $ks = [];
            foreach ($details as $detail) {
                $kartuStock = KartuStock::mutationStore(new Request([
                    'stock_id' => $detail->stock_id,
                    'mutasi_quantity' => $detail->quantity,
                    'unit' => $detail->unit,
                    'flow' => 1,
                    'code_group' => $coaPersediaan,
                    'mutasi_rupiah_total' => $detail->total_price,
                ]), false);
                if ($kartuStock['status'] == 0) {
                    throw new \Exception($kartuStock['msg']);
                }
                $ks[] = $kartuStock['msg'];
            }
            info(json_encode($ks));
            //oke sampek sini chat dan invoice sudah valid
            if ($coaPiutangKas > 120000) {
                //brati hutang, buat kartu hutang ya lur
                $kartu = KartuPiutang::createMutation(new Request([
                    'invoice_pack_number' => $invoicePack->invoice_number,
                    'amount_mutasi' => $invoicePack->total_price,
                    'person_id' => $invoicePack->person_id,
                    'person_type' => $invoicePack->person_type,
                    'code_group' => $coaPiutangKas,
                    'lawan_code_group' => $coaPenjualan,
                    'sales_order_number' => $salesOrder ? $salesOrder->sales_order_number : null,
                    'description' => 'penjualan nomer ' . $invoicePack->invoice_number,
                    'is_otomatis_jurnal' => 1,
                ]), $lockManager);
                if ($kartu['status'] == 0) {
                    throw new \Exception($kartu['msg']);
                }
                $kartuPiutang = $kartu['msg'];
                $journalNumberPenjualan = $kartuPiutang->journal_number;
            } else {
                //nah disini ini kalau ternyata lansung dibayar pakai kas lur

                $debets = [
                    [
                        'code_group' => $coaPenjualan,
                        'description' => 'penjualan nomer ' . $invoicePack->invoice_number,
                        'amount' => $invoicePack->total_price,
                        'toko_id' => $invoicePack->toko_id,
                        'reference_id' => null,
                        'reference_type' => null,
                    ],
                ];
                $kredits = [
                    [
                        'code_group' => $coaPiutangKas,
                        'description' => 'penjualan nomer ' . $invoicePack->invoice_number,
                        'amount' => $invoicePack->total_price,
                        'toko_id' => $invoicePack->toko_id,
                        'reference_id' => null,
                        'reference_type' => null,
                    ],
                ];
                $st = JournalController::createBaseJournal(new Request([
                    'kredits' => $kredits,
                    'debets' => $debets,
                    'type' => 'purchasing',
                    'date' => Date('Y-m-d H:i:s'),
                    'is_auto_generated' => 1,
                    'title' => 'create mutation purchase',
                    'url_try_again' => null

                ]), false, $lockManager);
                if ($st['status'] == 0) {
                    throw new \Exception($st['msg']);
                }
                $journalNumberPenjualan = $st['msg'];
            }

            info('journal number penjualan ' . $journalNumberPenjualan);
            //nah baru setelah ini kita buat jurnal persediaan dan harga pokok
            $amountPersediaan = abs(collect($ks)->sum('mutasi_rupiah_total'));
            $debets = [
                [
                    'code_group' => $coaBeban,
                    'description' => 'penjualan nomer ' . $invoicePack->invoice_number,
                    'amount' => $amountPersediaan,
                    'toko_id' => $invoicePack->toko_id,
                    'reference_id' => null,
                    'reference_type' => null,
                ],
            ];
            $kredits = [
                [
                    'code_group' => $coaPersediaan,
                    'description' => 'penjualan nomer ' . $invoicePack->invoice_number,
                    'amount' => $amountPersediaan,
                    'reference_id' => null,
                    'toko_id' => $invoicePack->toko_id,
                    'reference_type' => null,
                ],
            ];

            $st = JournalController::createBaseJournal(new Request([
                'kredits' => $kredits,
                'debets' => $debets,
                'type' => 'sales',
                'date' => Date('Y-m-d H:i:s'),
                'is_auto_generated' => 1,

                'title' => 'create mutation sales',
                'url_try_again' => null

            ]), false, $lockManager);
            if ($st['status'] == 0) {
                throw new \Exception($st['msg']);
            }

            $journalNumberPersediaan = $st['journal_number'];
            info('journal number persediaan ' . $journalNumberPersediaan);
            $journalPersediaan = Journal::where('journal_number', $journalNumberPersediaan)
                ->where('code_group', $coaPersediaan)->first();

            foreach ($ks as $k) {
                $kartu = KartuStock::find($k->id);
                $kartu->journal_id = $journalPersediaan->id;
                $kartu->journal_number = $journalPersediaan->journal_number;
                $kartu->save();
            }

            //mestinya sampek sini kartu stock dan hutang sudah jadi dan sudah integrate sama journal
            //saatnya bikin detail invoice kartu untuk semua jurnal yang telah dicreate
            $journals = Journal::whereIn('journal_number', [$journalNumberPenjualan, $journalNumberPersediaan])->get();
            foreach ($journals as $journal) {
                $journal->verifyJournal();
                $kartu = null;
                if ($journal->reference_model) {
                    $kartu = $journal->reference_model::where('journal_id', $journal->id)->first();
                }
                $kartuId = $kartu ? $kartu->id : null;
                $kartuType = $kartu ? get_class($kartu) : null;
                $dks = DetailKartuInvoice::storeData(new Request([
                    'kartu_type' => $kartuType,
                    'kartu_id' => $kartuId,
                    'invoice_pack_id' => $invoicePackID,
                    'journal_id' => $journal->id
                ]));
                if ($dks['status'] == 0) {
                    throw new \Exception($dks['msg']);
                }
            }
            DB::commit();
            $lockManager->releaseAll();
            return [
                'status' => 1,
                'msg' => 'Berhasil membuat claim penjualan',
                'kartu_piutang' => $kartuPiutang ?? null,
                'kartu_stock' => $ks,
                'journal' => $journals
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $lockManager->releaseAll();
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
    public function createClaimPembelian(Request $request)
    {
        $coaPersediaan = $request->input('coa_persediaan');
        $coaHutangKas = $request->input('coa_hutang_kas');
        $invoicePackID = $request->input('invoice_pack_id');
        $date = $request->input('date');
        $isBackdate = 0;
        if (carbonDate()->subMinutes(5) > $date) {
            //date lebih dari 5 menit dari sekarang, berarti backdate
            $isBackdate = 1;
        }
        $invoicePack = InvoicePack::find($invoicePackID);
        if (!$invoicePack) {
            return ['status' => 0, 'msg' => 'Invoice tidak ditemukan'];
        }
        $chartPersediaan = ChartAccount::where('code_group', $coaPersediaan)->first();
        $chartHutangKas = ChartAccount::where('code_group', $coaHutangKas)->first();
        if (!$chartPersediaan || !$chartHutangKas) {
            return ['status' => 0, 'msg' => 'Chart account tidak ditemukan'];
        }
        //buat kartu stock
        //dari sini apa yang sudah dibuat harus disimpan dulu. trus kalo gagal ditengah jalan kita rollback atau delete
        DB::beginTransaction();
        try {
            $details = $invoicePack->invoiceDetails();
            if (count($details) == 0) {
                throw new \Exception('Tidak ada detail invoice');
            }
            $ks = [];
            foreach ($details as $detail) {
                $stock = $detail->stock;
                if ($stock->name == 'custom') {
                    throw new \Exception('Tidak bisa membuat kartu "stock custom"');
                }
                $kartuStock = KartuStock::mutationStore(new Request([
                    'stock_id' => $detail->stock_id,
                    'mutasi_quantity' => $detail->quantity,
                    'unit' => $detail->unit,
                    'flow' => 0,
                    'code_group' => $coaPersediaan,
                    'is_custom_rupiah' => 1,
                    'mutasi_rupiah_total' => $detail->total_price,
                    'date' => $date
                ]), false);
                if ($kartuStock['status'] == 0) {
                    throw new \Exception($kartuStock['msg']);
                }
                $ks[] = $kartuStock['msg'];
            }
            info(json_encode($ks));
            //oke sampek sini chat dan invoice sudah valid
            if ($coaHutangKas > 200000) {
                //brati hutang, buat kartu hutang ya lur
                $kartu = KartuHutang::createMutation(new Request([
                    'invoice_pack_number' => $invoicePack->invoice_number,
                    'invoice_pack_id' => $invoicePackID,
                    'amount_mutasi' => $invoicePack->total_price,
                    'person_id' => $invoicePack->person_id,
                    'person_type' => $invoicePack->person_type,
                    'code_group' => $coaHutangKas,
                    'lawan_code_group' => $coaPersediaan,
                    'is_otomatis_jurnal' => 1,
                    'date' => $date,
                    'description' => 'pembelian ' . $invoicePack->person->name . ' nomer ' . $invoicePack->invoice_number,
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
                        'code_group' => $coaPersediaan,
                        'description' => 'pembelian ' . $invoicePack->person->name . ' nomer ' . $invoicePack->invoice_number,
                        'amount' => $invoicePack->total_price,
                        'reference_id' => null,
                        'reference_type' => null,

                    ],
                ];
                $kredits = [
                    [
                        'code_group' => $coaHutangKas,
                        'description' => 'pembelian nomer ' . $invoicePack->invoice_number,
                        'amount' => $invoicePack->total_price,
                        'reference_id' => null,
                        'reference_type' => null,
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

            $journalPersediaan = Journal::where('journal_number', $journalNumber)
                ->where('code_group', $coaPersediaan)->first();

            foreach ($ks as $k) {
                $kartu = KartuStock::find($k->id);
                $kartu->journal_id = $journalPersediaan->id;
                $kartu->journal_number = $journalPersediaan->journal_number;
                $kartu->save();
            }

            //mestinya sampek sini kartu stock dan hutang sudah jadi dan sudah integrate sama journal
            //saatnya bikin detail invoice kartu untuk semua jurnal yang telah dicreate
            $journals = Journal::where('journal_number', $journalNumber)->get();
            foreach ($journals as $journal) {
                $journal->verifyJournal();
                $kartu = null;
                if ($journal->reference_model) {
                    $kartu = $journal->reference_model::where('journal_id', $journal->id)->first();
                }
                $kartuId = $kartu ? $kartu->id : null;
                $kartuType = $kartu ? get_class($kartu) : null;
                $dks = DetailKartuInvoice::storeData(new Request([
                    'kartu_type' => $kartuType,
                    'kartu_id' => $kartuId,
                    'invoice_pack_id' => $invoicePackID,
                    'journal_id' => $journal->id
                ]));
                if ($dks['status'] == 0) {
                    throw new \Exception($dks['msg']);
                }
            }
            DB::commit();
            return [
                'status' => 1,
                'msg' => 'Berhasil membuat claim pembelian',
                'kartu_hutang' => $kartuHutang ?? null,
                'kartu_stock' => $ks,
                'journal' => $journals
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

    public function mark(Request $request)
    {
        $id = $request->input('id');
        $invoice = InvoicePack::find($id);
        $invoice->is_mark = !$invoice->is_mark ? 1 : 0;
        $invoice->save();
        return ['status' => 1, 'msg' => $invoice];
    }

    public function getItemInvoiceAktif($id)
    {
        $invoices = InvoicePack::where('sales_order_id', $id)
            ->where('invoice_number', 'like', '%' . getInput('search') . '%')
            ->select('invoice_number as text')->get()->map(function ($item) {
                $data = [
                    'id' => $item->text,
                    'text' => $item->text
                ];
                return $data;
            });

        return ['results' => $invoices];
    }
}
