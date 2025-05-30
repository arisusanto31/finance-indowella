<?php

namespace App\Http\Controllers;

use App\Models\BookJournal;
use App\Models\Customer;
use App\Models\InvoicePack;
use App\Models\KartuBahanJadi;
use App\Models\ManufSales;
use App\Models\ManufSalesPackage;
use App\Models\RetailSales;
use App\Models\RetailSalesPackage;
use App\Models\SaleOrderDetail;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\Stock;
use App\Models\StockCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Models\InvoiceSaleDetail;
use App\Models\KartuStock;
use Illuminate\Support\Facades\Log;


class SalesOrderController extends Controller
{
    //
    public function index()
    {
        $month = getInput('month') ? toDigit(getInput('month'), 2) : date('m');
        $year = getInput('year') ? getInput('year') : date('Y');
        $salesOrders = SalesOrderDetail::whereMonth('created_at', $month)
            ->whereYear('created_at', Date('Y'))->with('customer:name,id', 'stock:name,id', 'parent:sales_order_number,id,is_final,is_mark,total_price,ref_akun_cash_kind_name,status,status_payment,status_delivery')
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('sales_order_number');


        $invPack = SalesOrder::whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->select('is_final', 'is_mark', 'total_price')->get();
        $totalInvoice = collect($invPack)->sum('total_price');
        $totalInvoiceFinal = collect($invPack)->where('is_final', 1)->sum('total_price');
        $totalInvoiceMark = collect($invPack)->where('is_mark', 1)->sum('total_price');

        $parent = [];
        return view('invoice.sales-order', compact('salesOrders', 'month', 'year', 'totalInvoice', 'totalInvoiceFinal', 'totalInvoiceMark', 'parent'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'sales_order_number' => 'required|string|max:255',
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
                'detail_reference_id' => 'nullable|array',
                'detail_reference_id.*' => 'nullable|integer',
                'detail_reference_type' => 'nullable|array',
                'detail_reference_type.*' => 'nullable|string',
                'custom_stock_name' => 'nullable|array',
                'custom_stock_name.*' => 'nullable|string',
                'akun_cash_kind_name' => 'nullable|string',
            ]);
            $customerID = null;
            $arrayStockID = [];
            $detailReferenceID = $request->detail_reference_id ?? null;
            $detailReferenceType = $request->detail_reference_type ?? null;
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
            $sales_order_number = $request->sales_order_number . '-draft';
            $grouped = [];
            foreach ($arrayStockID as $i => $stockId) {
                $grouped[] = [
                    'sales_order_number' => $sales_order_number,
                    'stock_id' => $stockId,
                    'quantity' => $request->quantity[$i],
                    'unit' => $request->unit[$i],
                    'price' => $request->price_unit[$i],
                    'qtyjadi' => $request->qtyjadi[$i],
                    'unitjadi' => $request->unitjadi[$i],
                    'pricejadi' => $request->pricejadi[$i],
                    'discount' => $request->discount[$i] ?? 0,
                    'customer_id' => $customerID,
                    'book_journal_id' => bookID(),
                    'total_price' => format_db($request->total_price[$i]) ?? 0,
                    'toko_id' => $request->toko_id,
                    'custom_stock_name' => $request->custom_stock_name[$i] ?? null,
                    'created_at' => $request->input('created_at') ?? now(),
                    'reference_id' => $detailReferenceID ? $detailReferenceID[$i] : null,
                    'reference_type' => $detailReferenceType ? $detailReferenceType[$i] : null,
                ];
            }
            //create pack ya
            $invoicePack = SalesOrder::create([
                'sales_order_number' => $sales_order_number,
                'book_journal_id' => bookID(),
                'customer_id' => $customerID,
                'total_price' => collect($grouped)->sum('total_price'),
                'status' => 'draft',
                'toko_id' => $request->toko_id,
                'reference_id' => $request->input('reference_id'),
                'reference_type' => $request->input('reference_type'),
                'ref_akun_cash_kind_name' => $request->input('akun_cash_kind_name'),
                'created_at' => $request->input('created_at') ?? now(),
            ]);

            foreach ($grouped as $data) {
                $data['sales_order_id'] = $invoicePack->id;
                SalesOrderDetail::create($data);
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
        $id = $request->id;
        $salesOrder = SalesOrder::find($id);
        $details = SalesOrderDetail::where('sales_order_number', $salesOrder->sales_order_number)->get();
        $salesOrder->is_final = 1;
        $salesOrder->sales_order_number = $salesOrder->getCodeFix();
        foreach ($details as $detail) {
            //cek data
            if ($detail->qtyjadi == 0 && $detail->unitjadi == '??' && $detail->pricejadi == 0) {
                //brati ini data lama
                $typeSales = bookID() == 1 ? ManufSales::class : RetailSales::class;
                $referenceSale = $typeSales::where('package_id', $salesOrder->reference_id)
                    ->where('stock_name', $detail->custom_stock_name)->first();
                if ($referenceSale) {
                    $detail->reference_id = $referenceSale->id;
                    $detail->qtyjadi = $referenceSale->qtyjadi;
                    $detail->unitjadi = $referenceSale->unitjadi;
                    $detail->pricejadi = $referenceSale->pricejadi;
                    $detail->reference_type = $typeSales;
                } else {
                    return ['status' => 0, 'msg' => 'Tidak ditemukan data penjualan untuk stock ' . $detail->custom_stock_name];
                }
            }
            $detail->sales_order_number = $salesOrder->sales_order_number;
            $detail->save();
        }
        $salesOrder->save();
        return ['status' => 1, 'msg' => $salesOrder];
    }

    public function mark(Request $request)
    {
        $id = $request->input('id');
        $invoice = SalesOrder::find($id);
        $invoice->is_mark = !$invoice->is_mark ? 1 : 0;
        $invoice->save();
        return ['status' => 1, 'msg' => $invoice];
    }
    public function showDetail($number)
    {
        $data = SalesOrder::where('sales_order_number', $number)->first();

        $data->updateStatus();
        $invdetails = SalesOrderDetail::with('stock')->where('sales_order_number', $number)->get();
        foreach ($invdetails as $detail) {
            if ($detail->unitjadi == '??') {
                $detail->unitjadi = $detail->unit;
                $detail->save();
            }
        }
        $data['details'] = $invdetails;
        $data['kartus'] = $data->getAllKartu();
        $data['resume_total'] = $data->getTotalKartu();
        $view = view('invoice.modal._sale-detail');
        $view->data = $data;

        return $view;
    }

    function openImport()
    {
        $view = view('invoice.modal._sale_order_import');
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

            if (getInput('toko'))
                $sales = $sales->join('tokoes as t', 't.id', '=', 'pack.toko_id')
                    ->where('t.name', getInput('toko'));
            $sales = $sales->where(function ($q) {
                $q->where('pack.is_ppn', 1)->orWhere('pack.is_wajib_lapor', 1);
            })->select(
                'pack.id',
                DB::raw('"App\\\Models\\\RetailSalesPackage" as reference_type'),
                DB::raw('"App\\\Models\\\RetailStock" as stock_type'),
                'pack.is_ppn',
                'pack.is_wajib_lapor',
                'pack.package_number',
                'pack.akun_cash_kind_name',
                DB::raw('"anonim" as customer_name'),
                'pack.created_at',
            );
        } else {

            if (getInput('toko'))
                $sales = $sales->join('transactions as tr', 'tr.package_id', '=', 'pack.id')->where('tr.kind', getInput('toko'));
            $sales = $sales->where(function ($q) {
                $q->where('pack.is_ppn', 1)->orWhere('pack.is_wajib_lapor', 1);
            })->join('customers as c', 'c.id', '=', 'pack.customer_id');
            if (getInput('customer')) {
                $sales = $sales->where(function ($q) {
                    $q->where('c.name', 'like', '%' . getInput('customer') . '%')
                        ->orWhere('c.instance', 'like', '%' . getInput('customer') . '%');
                });
            }
            $sales = $sales->select(
                'pack.id',
                DB::raw('"App\\\Models\\\ManufSalesPackage" as reference_type'),
                DB::raw('"App\\\Models\\\ManufStock" as stock_type'),
                'pack.is_ppn',
                'pack.is_wajib_lapor',
                'pack.package_number',
                'pack.customer_id',
                'pack.akun_cash_kind_name',
                'c.instance as customer_name',
                'pack.created_at',
            );
        }

        $sales = $sales->with('detailSales')->get()->map(function ($val) use ($modeBook) {
            $val['details'] = collect($val['detailSales'])->map(function ($detailVal) use ($modeBook) {
                $data = [];
                $data['stock_id'] = $detailVal['stock_id'];
                $data['quantity'] = $detailVal['quantity'];
                $data['unit'] = $modeBook == 'toko' ? $detailVal['unit'] : $detailVal['unit_info'];
                $data['price'] = $detailVal['recent_selling_price'];
                $data['qtyjadi'] = $detailVal['qtyjadi'] ?? $detailVal['quantity'];
                $data['pricejadi'] = $detailVal['pricejadi'] ?? $detailVal['recent_selling_price'];
                $data['unitjadi'] = $detailVal['unitjadi'] ?? $data['unit'];
                $data['total_price'] = $detailVal['total'];
                $data['discount'] = $detailVal['discount'];
                $data['customer_id'] = $detailVal['customer_id'];
                $data['stock_name'] = $detailVal['stock_name'];
                $data['reference_id'] = $detailVal['id'] ?? null;
                $data['reference_type'] = $modeBook == 'toko' ? 'App\Models\RetailSales' : 'App\Models\ManufSales';
                $data['toko'] = $modeBook == 'toko' ? $detailVal->toko->name : $detailVal->kind;
                return $data;
            });
            return collect($val)->only('id', 'reference_type', 'is_ppn', 'customer_name', 'is_wajib_lapor', 'details', 'package_number', 'stock_type', 'akun_cash_kind_name', 'created_at');
        });

        return ['status' => 1, 'msg' => $sales];
    }


    public function getInfoReferenceFinish(Request $request)
    {
        $sales = SalesOrder::whereIn('id', $request->ids)->with('reference')->get()->map(function ($val) {
            $finished_at = null;
            if ($val->reference) {
                $finished_at = createCarbon($val->reference->delivery_at)->format("Y-m-d");
            }
            return [
                'id' => $val->id,
                'finished_at' => $finished_at
            ];
        });
        return ['status' => 1, 'msg' => $sales];
    }

    public function editInvoice($number)
    {
        $data = SalesOrder::where('sales_order_number', $number)->first();

        $data->updateStatus();
        $invdetails = SalesOrderDetail::with('stock')->where('sales_order_number', $number)->get();
        foreach ($invdetails as $detail) {
            if ($detail->qtyjadi == 0 && $detail->unitjadi == '??' && $detail->pricejadi == 0) {
                //brati ini data lama
                $typeSales = bookID() == 1 ? ManufSales::class : RetailSales::class;
                $referenceSale = $typeSales::where('package_id', $data->reference_id)
                    ->where('stock_name', $detail->custom_stock_name)->first();
                if ($referenceSale) {
                    $detail->reference_id = $referenceSale->id;
                    $detail->qtyjadi = $referenceSale->qtyjadi - $referenceSale->qtyrefund;
                    $detail->unitjadi = $referenceSale->unitjadi ? $referenceSale->unitjadi : $referenceSale->unit_info;
                    $detail->pricejadi = $referenceSale->pricejadi;
                    $detail->quantity = $referenceSale->qtybahan + $referenceSale->insheet;
                    $detail->reference_type = $typeSales;
                } else {
                    return ['status' => 0, 'msg' => 'Tidak ditemukan data penjualan untuk stock ' . $detail->custom_stock_name];
                }
            }
        }
        $data['details'] = $invdetails;
        $view = view('invoice.modal._sale-edit');
        $view->data = $data;
        return $view;
    }

    private function normalizeDate($input)
    {
        $clean = str_replace(['/', '.'], '-', $input);
        $parts = explode('-', $clean);

        if (count($parts) !== 3) return null;

        [$a, $b, $c] = $parts;

        if (strlen($a) === 4) {
            return sprintf('%04d-%02d-%02d', $a, $b, $c);
        }

        if (strlen($c) === 4) {
            if ((int)$b > 12) {
                return sprintf('%04d-%02d-%02d', $c, $a, $b);
            } else {
                return sprintf('%04d-%02d-%02d', $c, $b, $a);
            }
        }

        return null;
    }


    public function updateDetail(Request $request)
    {
        try {
            Log::info('Masuk ke updateDetail', $request->all());

            DB::beginTransaction();

            if (empty($request->sales_order_number)) {
                Log::warning('Nomor Sales Order kosong!');
                return response()->json(['status' => 'error', 'message' => 'Nomor Sales Order tidak boleh kosong']);
            }

            $firstDetail = \App\Models\SalesOrderDetail::find($request->detail_id[0]);
            if (!$firstDetail) {
                Log::error('Detail tidak ditemukan');
                return response()->json(['status' => 'error', 'message' => 'Detail tidak ditemukan']);
            }


            $salesOrder = \App\Models\SalesOrder::find($firstDetail->sales_order_id);
            if (!$salesOrder) {
                Log::error('Sales Order tidak ditemukan');
                return response()->json(['status' => 'error', 'message' => 'Sales Order tidak ditemukan']);
            }


            $tanggalGlobal = $this->normalizeDate($request->tanggal_global);


            $salesOrder->sales_order_number = $request->sales_order_number;
            $salesOrder->created_at = $tanggalGlobal;



            Log::info("Sales order {$salesOrder->id} updated dengan nomor: {$request->sales_order_number}, tanggal: {$tanggalGlobal}");

            $totalBaru = 0;


            foreach ($request->detail_id as $index => $id) {
                $detail = \App\Models\SalesOrderDetail::find($id);

                if ($detail) {
                    $qty     = $request->quantity[$index] ?? 0;
                    $price   = $request->price[$index] ?? 0;
                    $disc    = $request->discount[$index] ?? 0;
                    $unit    = $request->unit[$index] ?? $detail->unit;

                    $total   = ($qty * $price) - $disc;

                    $detail->qtyjadi      = $qty;
                    $detail->pricejadi       = $price;
                    $detail->discount      = $disc;
                    $detail->unit          = $unit;
                    $detail->total_price   = $total;
                    $detail->price = ($total) / $detail->quantity;
                    $detail->sales_order_number = $request->sales_order_number;

                    $detail->created_at = $tanggalGlobal;


                    if (!$detail->save()) {
                        Log::error("❌ Gagal menyimpan detail ID: {$detail->id}");
                    } else {
                        Log::info("✅ Detail ID {$detail->id} berhasil disimpan");
                    }

                    $totalBaru += $total;
                } else {
                    Log::warning("Detail ID {$id} tidak ditemukan.");
                }
            }


            $salesOrder->total_price = $totalBaru;
            $salesOrder->save();

            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Exception saat update sales order:', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server',
            ], 500);
        }
    }

    public function hitungReferenceBiaya(Request $request)
    {
        $ids = $request->input('ids');
        $salesOrders = SalesOrderDetail::whereIn('id', $ids)->get();
        $alldata=[];
        foreach ($salesOrders as $detail) {
            $data = [];
            $reference = $detail->reference;
            if ($detail->reference_type == RetailSales::class) {
                $hpp = $reference->hpp * $reference->quantity;
                $subkon = 0;
            } else if ($detail->reference_type == ManufSales::class) {
                $hpp = $reference->detailInvoices->sum('total_hpp');
                $subkon = $reference->detailInvoices->sum('total_subkon');
            } else {
                $hpp = 0;
                $subkon = 0;
            }
            $data['id'] = $detail->id;
            $data['hpp'] = $hpp;
            $data['subkon'] = $subkon;
            $alldata[] = $data;
        }
        return [
            'status' => 1,
            'msg' => $alldata
        ];
    }

    public function kebutuhanProduksiMarked($data)
    {
        //disini data itu dari btoa jadi harus di dekrip
        $data = json_decode(base64_decode($data), true);
        $sales = SalesOrder::from('sales_orders as so')->join('sales_order_details as sds', 'sds.sales_order_number', '=', 'so.sales_order_number')->whereIn('so.id', $data)
            ->join('stock_units as theunit', function ($join) {
                $join->on('sds.stock_id', '=', 'theunit.stock_id')
                    ->on('sds.unit', '=', 'theunit.unit');
            })
            ->join('stocks as s', 's.id', '=', 'sds.stock_id')
            ->select('s.name', DB::raw('sds.quantity * theunit.konversi as qtybackend'), 's.id', 'theunit.konversi', 's.unit_backend as unitbackend')->get()
            ->groupBy('id')->map(function ($val) {
                $data = [];
                $data['id'] = $val[0]->id;
                $data['name'] = $val[0]->name;
                $data['unit'] = $val[0]->unitbackend;
                $data['quantity'] = collect($val)->sum('qtybackend');
                return $data;
            });

        $allstockid = $sales->keys()->all();

        $sisaStock = KartuStock::whereIn('index_date', function ($q) use ($allstockid) {
            $q->from('kartu_stocks')->whereIn('stock_id', $allstockid)->where('book_journal_id', bookID())->select(DB::raw('max(index_date) as maxindexdate'))
                ->groupBy('stock_id');
        })->select('stock_id', 'saldo_qty_backend')->get()
            ->pluck('saldo_qty_backend', 'stock_id')->all();

        $view = view('invoice.kebutuhan-produksi');
        $view->kebutuhanProduksi = $sales->values()->all();
        $view->sisaStock = $sisaStock;

        return $view;
        // ->groupBy('stock_id')->map(function ($val) {
        //disini itung jumlah. tapi pastikan satuannya sama ya lur.
        // });
    }



    public function updateInputInvoice($number)
    {
        $bahanJadi = KartuBahanJadi::whereIn('id', function ($q) use ($number) {
            $q->from('kartu_bahan_jadis')->select(DB::raw('max(id)'))
                ->where('sales_order_number', $number)
                ->groupBy('stock_id', 'sales_order_number');
        })->where('sales_order_number', $number)->get()->keyBy('stock_id');
        $details = SalesOrderDetail::where('sales_order_number', $number)->get();

        return [
            'status' => 1,
            'msg' => $details,
            'bahan_jadi' => $bahanJadi,
        ];
    }

    function destroy($number)
    {
        $so = SalesOrder::where('sales_order_number', $number)->first();
        $details = $so->details;
        $kartus = $so->detailKartuInvoices;
        if (count($kartus) > 0) {
            return ['status' => 0, 'msg' => 'Tidak bisa menghapus data ini karena sudah ada proses'];
        }
        foreach ($details as $detail) {
            $detail->delete();
        }
        $so->delete();
        return ['status' => 1, 'msg' => 'data berhasil dihapus'];
    }
}
