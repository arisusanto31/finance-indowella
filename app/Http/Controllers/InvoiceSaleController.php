<?php

namespace App\Http\Controllers;

use App\Models\BookJournal;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\InvoiceSaleDetail;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\InvoicePack;
use App\Models\Journal;
use App\Models\KartuBahanJadi;
use App\Models\KartuDPSales;
use App\Models\KartuPiutang;
use App\Models\ManufSales;
use App\Models\ManufSalesPackage;
use App\Models\RetailSales;
use App\Models\RetailSalesPackage;
use App\Models\SalesOrder;
use App\Services\LockManager;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Models\InvoiceSale;
use Illuminate\Support\Facades\Log;




class InvoiceSaleController extends Controller
{



    private function normalizeDate($input)
    {
        $clean = str_replace(['/', '.'], '-', $input);
        $parts = explode('-', $clean);
        if (count($parts) !== 3) return null;

        [$a, $b, $c] = $parts;

        if (strlen($a) === 4) {
            return sprintf('%04d-%02d-%02d', $a, $b, $c); // yyyy-mm-dd
        }

        if (strlen($c) === 4) {
            if ((int)$b >= 12) {
                return sprintf('%04d-%02d-%02d', $c, $a, $b); // mm-dd-yyyy → yyyy-mm-dd
            } else {
                return sprintf('%04d-%02d-%02d', $c, $b, $a); // dd-mm-yyyy → yyyy-mm-dd
            }
        }

        return null;
    }

    public function updateInvoiceSales(Request $request, $originalInvoiceNumber)
    {

        $detailIds = $request->input('detail_id');
        $quantities = $request->input('quantity');
        $prices = $request->input('price');
        $discounts = $request->input('discount');
        $newInvoiceNumber = $request->input('sales_order_number_pack');
        $tanggal = $this->normalizeDate($request->input('tanggal_global'));

        // return [$originalInvoiceNumber, $newInvoiceNumber];

        if (empty($detailIds)) {
            return response()->json(['success' => false, 'message' => 'Data detail kosong']);
        }

        DB::beginTransaction();

        try {
            InvoiceSaleDetail::where('invoice_pack_number', $originalInvoiceNumber)
                ->update(['invoice_pack_number' => $newInvoiceNumber]);

            $totalPrice = 0;

            foreach ($detailIds as $index => $id) {
                $detail = InvoiceSaleDetail::find($id);
                if ($detail) {
                    $qty = $quantities[$index] ?? 0;
                    $price = $prices[$index] ?? 0;
                    $disc = $discounts[$index] ?? 0;
                    $total = ($qty * $price) - $disc;

                    $detail->update([
                        'quantity' => $qty,
                        'price' => $price,
                        'discount' => $disc,
                        'invoice_pack_number' => $newInvoiceNumber,
                        'created_at' => $tanggal,
                    ]);

                    $totalPrice += $total;
                }
            }

            InvoicePack::where('invoice_number', $originalInvoiceNumber)->update([
                'invoice_number' => $newInvoiceNumber,
                'created_at' => $tanggal,
                'total_price' => $totalPrice,
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Gagal update invoice sales: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
            ], 500);
        }
    }




    public function editInvoiceSales($invoiceNumber)
    {
        $data = InvoicePack::where('invoice_number', $invoiceNumber)->first();

        $details = InvoiceSaleDetail::with('stock')
            ->where('invoice_pack_id', $data->id)
            ->get();



        $data['details'] = $details;


        $view = view('invoice.modal._edit-sales');
        $view->data = $data;

        return $view;
    }



    //     public function editInvoiceSales($invoiceNumber)
    // {

    //     $data = InvoicePack::where('invoice_number', $invoiceNumber)->first();
    //     $details = InvoiceSaleDetail::with('stock')
    //                 ->where('invoice_pack_number', $invoiceNumber)
    //                 ->get();

    //     $data['details'] = $details;

    //     $view = view('invoice.modal._edit-sales');
    //     $view->data = $data;

    //     return $view;
    // }




    public function showSales()
    {
        $month = getInput('month') ? toDigit(getInput('month'), 2) : date('m');
        $year = getInput('year') ? getInput('year') : date('Y');

        $invoices = InvoiceSaleDetail::whereMonth('created_at', $month)->whereYear('created_at', $year)->with('customer', 'stock', 'parent')
            ->get()
            ->groupBy('invoice_pack_number');
        $invPack = InvoicePack::whereMonth('created_at', $month)->whereYear('created_at', $year)->where('reference_model', InvoiceSaleDetail::class)
            ->select('is_final', 'is_mark', 'total_price')->get();
        $totalInvoice = collect($invPack)->sum('total_price');
        $totalInvoiceFinal = collect($invPack)->where('is_final', 1)->sum('total_price');
        $totalInvoiceMark = collect($invPack)->where('is_mark', 1)->sum('total_price');
        $parent = [];

        // dd($invoices);
        return view('invoice.invoice-sales', compact('invoices', 'month', 'year', 'totalInvoice', 'totalInvoiceFinal', 'totalInvoiceMark', 'parent'));
    }

    // public function showSales()
    // {
    //     $invoices = InvoiceSaleDetail::latest()->get();
    //     $categories = \App\Models\StockCategory::all();
    //     $stocks = Stock::all();
    //     $categories = StockCategory::all();
    //     $stocks = Stock::with(['category', 'parentCategory', 'units'])->get();
    //     return view('invoice.invoice-sales', compact('invoices','stocks','categories'));
    // }
    //fungsi ini bisa  store manual dan dari import data
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'invoice_number' => 'required|string|max:255',
                'customer_id' => 'nullable|integer',
                'customer_name' => 'nullable|string|max:255',
                'reference_stock_id' => 'nullable|array',
                'reference_stock_id.*' => 'nullable|integer',
                'reference_stock_type' => 'nullable|string',
                'stock_id' => 'nullable|array',
                'stock_id.*' => 'nullable|integer',
                'quantity' => 'required|array',
                'quantity.*' => 'required|numeric',
                'price_unit' => 'required|array',
                'price_unit.*' => 'required|numeric',
                'unit' => 'required|array',
                'unit.*' => 'required|string',
                'total_price' => 'required|array',
                'total_price.*' => 'required|string',
                'toko_id' => 'required|integer',
                'reference_id' => 'nullable|integer',
                'reference_type' => 'nullable|string',
                'custom_stock_name' => 'nullable|array',
                'custom_stock_name.*' => 'nullable|string|max:255',
            ]);
            $customerID = null;
            $arrayStockID = [];
            if (!$request->stock_id) {
                if ($request->reference_stock_id) {
                    foreach ($request->reference_stock_id as $row => $refStockID) {
                        $refType = $request->reference_stock_type;
                        $stock = Stock::where('reference_stock_type', $refType)->where('reference_stock_id', $refStockID)->first();
                        if (!$stock) {
                            $refStock = $refType::find($refStockID);
                            if ($refStock) {
                                $stat = StockController::sync(new Request([
                                    'data' => [
                                        'id' => $refStock->id,
                                        'name' => $refStock->name,
                                        'unit_backend' => $refStock->unit_backend,
                                        'unit_default' => $refStock->unit_info,
                                        'units_manual' => $refStock->getUnits(),
                                        'category_id' => $refStock->category_id,
                                        'category' => collect($refStock->category)->only('id', 'name'),
                                        'parent_category_id' => $refStock->parent_category_id,
                                        'parent_category' => collect($refStock->parentCategory)->only('id', 'name'),
                                        'master_stock_id' => null,
                                    ],
                                    'stock_id' => $refStock->id,
                                ]));
                                if ($stat['status'] == 1) {
                                    $stock = $stat['msg'];
                                } else {
                                    return ['status' => 0, 'msg' => 'pembuatan stock ' . $refStock->name . ' gagal,' . $stat['msg']];
                                }
                            } else {
                                return ['status' => 0, 'msg' => 'Stock tidak ditemukan'];
                            }
                        }
                        $arrayStockID[] = $stock->id;
                    }
                } else {
                    return ['status' => 0, 'msg' => 'Stock harus diisi'];
                }
            } else {
                $arrayStockID = $request->stock_id;
            }

            if (!$request->customer_id) {
                if ($request->customer_name) {
                    $customer = Customer::where('name', $request->customer_name)->first();
                    if (!$customer) {
                        $customer = Customer::create([
                            'name' => $request->customer_name,
                        ]);
                    }
                    $customerID = $customer->id;
                } else {
                    return ['status' => 0, 'msg' => 'Customer harus diisi'];
                }
            } else {
                $customerID = $request->customer_id;
            }

            $invoiceNumber = $request->invoice_number . '-draft';
            //invoice number ini harus otomatis
            $grouped = [];

            foreach ($arrayStockID as $i => $stockId) {
                $grouped[] = [
                    'invoice_pack_number' => $invoiceNumber,
                    'stock_id' => $stockId,
                    'quantity' => $request->quantity[$i],
                    'unit' => $request->unit[$i],
                    'price' => $request->price_unit[$i],
                    'discount' => $request->discount[$i] ?? 0,
                    'customer_id' => $customerID,
                    'book_journal_id' => bookID(),
                    'total_price' => format_db($request->total_price[$i]) ?? 0,
                    'toko_id' => $request->toko_id,
                    'custom_stock_name' => $request->custom_stock_name[$i] ?? null,
                    'created_at' => $request->input('created_at') ?? now(),
                ];
            }

            //create pack ya
            $invoicePack = InvoicePack::create([
                'invoice_number' => $invoiceNumber,
                'book_journal_id' => bookID(),
                'person_id' => $customerID,
                'person_type' => Customer::class,
                'total_price' => collect($grouped)->sum('total_price'),
                'status' => 'draft',
                'toko_id' => $request->toko_id,
                'reference_id' => $request->input('reference_id'),
                'reference_type' => $request->input('reference_type'),
                'reference_model' => InvoiceSaleDetail::class,
                'created_at' => $request->input('created_at') ?? now(),
            ]);

            foreach ($grouped as $data) {
                $data['invoice_pack_id'] = $invoicePack->id;
                InvoiceSaleDetail::create($data);
            }
            DB::commit();
            return ['status' => 1, 'msg' => 'Data berhasil disimpan'];
        } catch (Throwable $e) {
            DB::rollBack();
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }


    public function makeFinal(Request $request)
    {
        $id = $request->input('id');
        $inv = InvoicePack::find($id);
        // return ['status' => 0, 'msg' => $inv];
        $details = $inv->reference_model::where('invoice_pack_number', $inv->invoice_number)->get();
        $inv->is_final = 1;
        $inv->invoice_number = $inv->getCodeFix();
        foreach ($details as $detail) {
            $detail->invoice_pack_number = $inv->invoice_number;
            $detail->save();
        }
        $inv->updateStatus();
        return ['status' => 1, 'msg' => $inv];
    }

    //fungsi ini untuk create invoice dari Sales Order
    public function createInvoices(Request $request)
    {
        $lockManager = new LockManager();
        // return ['status' => 0, 'msg' => $request->all()];
        $codeGroupPenjualans = $request->input('code_group_penjualan');
        $customStockNames = $request->input('custom_stock_name');
        $salesOrderID = $request->input('sales_order_id');
        $salesOrderNumber = $request->input('sales_order_number');
        $hpps = $request->input('hpp');
        $codeGroupPiutangs = $request->input('code_group_piutang');
        $quantities = $request->input('quantity');
        $units = $request->input('unit');
        $date = $request->input('date');
        $stockIDs = $request->input('stock_id');
        $productionNumbers = $request->input('production_number');
        $sales = SalesOrder::find($salesOrderID);
        $salesDetailIDs = $request->input('sales_detail_id');
        DB::beginTransaction();
        try {
            $lastInv = InvoicePack::where('person_id', $sales->customer_id)
                ->where('person_type', Customer::class)->orderBy('id', 'desc')->first();
            $count = $lastInv ? intval(explode('-', $lastInv->invoice_number)[2]) : 0;
            $invoiceNumber = sprintf("INV-%04d-%03d", $sales->customer_id, $count + 1);
            $realDataSales = $sales->details->keyBy('id')->all();
            foreach ($salesDetailIDs as $i => $saleDetailID) {
                $dataDetailSale = $realDataSales[$saleDetailID];
                $discount = $quantities[$i] * $dataDetailSale->discount / $dataDetailSale->quantity;
                $grouped[] = [
                    'invoice_pack_number' => $invoiceNumber,
                    'stock_id' => $stockIDs[$i],
                    'quantity' => $quantities[$i],
                    'unit' => $units[$i],
                    'price' => $dataDetailSale->pricejadi,
                    'discount' =>  $discount,
                    'customer_id' => $sales->customer_id,
                    'sales_order_id' => $saleDetailID,
                    'sales_order_number' => $salesOrderNumber,
                    'book_journal_id' => bookID(),
                    'total_price' => $dataDetailSale->total_price,
                    'toko_id' => $dataDetailSale->toko_id,
                    'custom_stock_name' => $customStockNames[$i] ?? null,

                ];
            }
            $invoicePack = InvoicePack::create([
                'invoice_number' => $invoiceNumber,
                'book_journal_id' => bookID(),
                'person_id' => $sales->customer_id,
                'person_type' => Customer::class,
                'total_price' => collect($grouped)->sum('total_price'),
                'status' => 'draft',
                'toko_id' => $sales->toko_id,
                'sales_order_id' => $salesOrderID,
                'reference_id' => null,
                'reference_type' => null,
                'reference_model' => InvoiceSaleDetail::class,
            ]);
            $invoicePack->invoice_number = $invoicePack->getCodeFix();
            $invoicePack->is_final = 1;
            $invoicePack->save();
            //create pack ya
            $details = [];
            foreach ($grouped as $data) {
                $data['invoice_pack_number'] = $invoicePack->invoice_number;
                $data['invoice_pack_id'] = $invoicePack->id;
                $details[] = InvoiceSaleDetail::create($data);
            }



            foreach ($salesDetailIDs as $i => $saleDetailID) {
                $dataDetailSale = $realDataSales[$saleDetailID];
                $person = $invoicePack->person;
                $kartu = KartuPiutang::createMutation(new Request([
                    'invoice_pack_number' => $invoicePack->invoice_number,
                    'amount_mutasi' => $dataDetailSale->total_price,
                    'person_id' => $invoicePack->person_id,
                    'person_type' => $invoicePack->person_type,
                    'code_group' => $codeGroupPiutangs[$i],
                    'lawan_code_group' => $codeGroupPenjualans[$i],
                    'sales_order_number' => $salesOrderNumber,
                    'is_otomatis_jurnal' => 1,
                    'description' => 'penjualan ' . $person->name . ' ' . $invoicePack->invoice_number . ' item-' . ($i + 1),
                    'date' => $date,
                ]), $lockManager);
                if ($kartu['status'] == 0) {
                    throw new \Exception($kartu['msg']);
                }
                $journalNumber = $kartu['msg']->journal_number;
                $journal = Journal::where('journal_number', $journalNumber)->where('code_group', $codeGroupPenjualans[$i])->first();
                $journalID = $journal ? $journal->id : null;
                $dataSaleDetail = InvoiceSaleDetail::where('custom_stock_name', $customStockNames[$i])->where('invoice_pack_number', $invoicePack->invoice_number)->first();

                $dataSaleDetail->journal_id = $journalID;
                $dataSaleDetail->journal_number = $journalNumber;
                $dataSaleDetail->save();
                $dataSaleDetail->createDetailkartuInvoice();

                //disini harusnya udah jadi jurnal piutang dan penjualannya
                //tinggal hubungkan jurnal penjualannya ke kartu penjualannya

                $st = KartuBahanJadi::mutationStore(new Request([
                    'stock_id' => $stockIDs[$i],
                    'mutasi_quantity' => $quantities[$i],
                    'unit' => $units[$i],
                    'flow' => 1, //keluar
                    'sales_order_number' => $salesOrderNumber,
                    'production_number' => $productionNumbers[$i],
                    'sales_order_id' => $salesOrderID,
                    'code_group' => 140004,
                    'custom_stock_name' => $customStockNames[$i],
                    'lawan_code_group' => 601000, //hpp
                    'is_otomatis_jurnal' => 1,
                    'date' => $date
                ]), false, $lockManager);
                if ($st['status'] == 0) {
                    throw new \Exception($st['msg']);
                }
            }

            DB::commit();
            $lockManager->releaseAll();
            //buat jurnal penjualan
            return ['status' => 1, 'pack' => $invoicePack, 'details' => $details];
        } catch (Throwable $th) {
            DB::rollBack();
            $lockManager->releaseAll();
            return ['status' => 0, 'msg' => $th->getMessage()];
        }
    }

    function submitBayarSalesInvoice(Request $request)
    {
        $lockManager = new LockManager();
        $codeGroupPiutang = $request->input('codegroup_piutang');
        $codeGroupBayar = $request->input('codegroup_bayar');
        $invoiceNumber = $request->input('invoice_number');
        $amount = $request->input('amount');
        $date = $request->input('date');

        DB::beginTransaction();
        try {
            $invoicePack = InvoicePack::where('invoice_number', $invoiceNumber)->first();
            if (!$invoicePack) {
                throw new \Exception('Invoice tidak ditemukan');
            }
            $sales = SalesOrder::find($invoicePack->sales_order_id);
            $kartu = KartuPiutang::createPelunasan(new Request([
                'invoice_pack_number' => $invoiceNumber,
                'amount_bayar' => $amount,
                'person_id' => $invoicePack->person_id,
                'person_type' => $invoicePack->person_type,
                'code_group' => $codeGroupPiutang,
                'lawan_code_group' => $codeGroupBayar,
                'sales_order_number' => $sales->sales_order_number,
                'is_otomatis_jurnal' => 1,
                'date' => $date,
                'description' => 'pelunasan piutang dari invoice ' . $invoicePack->invoice_number,
            ]), $lockManager);
            if ($kartu['status'] == 0) {
                throw new \Exception($kartu['msg']);
            }
            $journalNumber = $kartu['journal_number'];
            $journal = Journal::where('journal_number', $journalNumber)->where('code_group', $codeGroupBayar)->first();
            $journalID = $journal ? $journal->id : null;
            if ($codeGroupBayar == 214000) {
                //uang muka penjualan
                $dpsales = KartuDPSales::createPelunasan(new Request([
                    'invoice_pack_number' => $invoiceNumber,
                    'amount_bayar' => $amount,
                    'person_id' => $invoicePack->person_id,
                    'person_type' => $invoicePack->person_type,
                    'code_group' => 214000,
                    'lawan_code_group' => $codeGroupPiutang,
                    'sales_order_number' => $sales->sales_order_number,
                    'is_otomatis_jurnal' => 0,
                    'date' => $date,
                    'description' => 'pelunasan piutang dari invoice ' . $invoicePack->invoice_number,
                ]), $lockManager);
                if ($dpsales['status'] == 0) {
                    throw new \Exception($dpsales['msg']);
                }
                $kartuDPSales = $dpsales['msg'];
                $kartuDPSales->journal_id = $journalID;
                $kartuDPSales->journal_number = $journalNumber;
                $kartuDPSales->save();
                $kartuDPSales->createDetailKartuInvoice();
            }
            DB::commit();
            $lockManager->releaseAll();
            return [
                'status' => 1,
                'msg' => $kartu['msg'],
            ];
        } catch (Throwable $th) {
            DB::rollBack();
            $lockManager->releaseAll();
            return ['status' => 0, 'msg' => $th->getMessage()];
        }
    }


    function openImport($book_journal_id)
    {


        $view = view('invoice.modal._invoice_sale_import');

        return $view;
    }

    public function getDataImport($book_journal_id)
    {
        $monthYear = getInput('monthyear') ? getInput('monthyear') : Date('Y-m');
        $date = createCarbon($monthYear);
        $month = $date->format('m');
        $year = $date->format('Y');
        $book = BookJournal::find($book_journal_id);
        if (!$book) {
            return ['status' => 0, 'msg' => 'Book tidak ditemukan'];
        }
        $defaultDB = config('database.connections.mysql.database');
        $saleModel = $book->name == 'Buku Toko' ? RetailSalesPackage::class : ManufSalesPackage::class;
        $modeBook = $book->name == 'Buku Toko' ? 'toko' : 'manuf';
        $sales = $saleModel::from('packages as pack')
            ->leftJoin($defaultDB . '.invoice_packs as inv', function ($join) use ($saleModel) {
                $join->on('pack.id', '=', 'inv.reference_id')
                    ->where('inv.reference_type', $saleModel);
            })
            ->leftJoin($defaultDB . '.sales_orders as so', function ($join) use ($saleModel) {
                $join->on('pack.id', '=', 'so.reference_id')
                    ->where('so.reference_type', $saleModel);
            })
            ->whereNull('inv.id')->whereNull('so.id')->whereMonth('pack.created_at', $month)->whereYear('pack.created_at', $year);
        if ($book->name == 'Buku Toko') {
            // la disini ngambil data yang dari toko aja yaa
            if (getInput('toko'))
                $sales = $sales->join('tokoes as t', 't.id', '=', 'pack.toko_id')
                    ->where('t.name', getInput('toko'));

            $sales = $sales->where(function ($q) {
                $q->where('pack.is_ppn', 1)->orWhere('pack.is_wajib_lapor', 1);
            })
                ->select(
                    'pack.id',
                    DB::raw('"App\\\Models\\\RetailSalesPackage" as reference_type'),
                    DB::raw('"App\\\Models\\\RetailStock" as stock_type'),
                    'pack.is_ppn',
                    'pack.is_wajib_lapor',
                    'pack.package_number',
                    'pack.akun_cash_kind_name',
                    DB::raw('"anonim" as customer_name'),
                    'pack.created_at'
                );
        } else {

            if (getInput('toko'))
                $sales = $sales->join('transactions as tr', 'tr.package_id', '=', 'pack.id')->where('tr.kind', getInput('toko'));
            $sales = $sales->where(function ($q) {
                $q->where('pack.is_ppn', 1)->orWhere('pack.is_wajib_lapor', 1);
            })->join('customers as c', 'c.id', '=', 'pack.customer_id')->select(
                'pack.id',
                DB::raw('"App\\\Models\\\ManufSalesPackage" as reference_type'),
                DB::raw('"App\\\Models\\\ManufStock" as stock_type'),
                'pack.is_ppn',
                'pack.is_wajib_lapor',
                'pack.package_number',
                'pack.customer_id',
                'pack.akun_cash_kind_name',
                'c.instance as customer_name',
                'pack.created_at'
            );
        }

        $sales = $sales->with('detailSales')->get()->map(function ($val) use ($modeBook) {
            $val['details'] = collect($val['detailSales'])->map(function ($detailVal) use ($modeBook) {
                $data = [];

                $data['stock_id'] = $detailVal['stock_id'];
                $data['quantity'] = $detailVal['quantity'];
                $data['unit'] = $modeBook == 'toko' ? $detailVal['unit'] : $detailVal['unit_info'];
                $data['price'] = $detailVal['recent_selling_price'];
                $data['total_price'] = $detailVal['total'];
                $data['discount'] = $detailVal['discount'];
                $data['customer_id'] = $detailVal['customer_id'];
                $data['stock_name'] = $detailVal['stock_name'];
                $data['toko'] = $modeBook == 'toko' ? $detailVal->toko->name : $detailVal->kind;
                return $data;
            });
            return collect($val)->only('id', 'reference_type', 'is_ppn', 'customer_name', 'is_wajib_lapor', 'details', 'package_number', 'stock_type', 'akun_cash_kind_name', 'created_at');
        });

        return ['status' => 1, 'msg' => $sales];
    }

    public function refresh($id)
    {
        try {
            $invoiceSale = InvoiceSaleDetail::find($id);
            $invoiceSale->createDetailKartuInvoice();
            return ['status' => 1, 'msg' => $invoiceSale];
        } catch (Throwable $e) {
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }
}
