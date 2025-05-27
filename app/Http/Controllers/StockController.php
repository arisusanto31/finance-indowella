<?php

namespace App\Http\Controllers;

use App\Models\BookJournal;
use App\Models\ManufStock;
use App\Models\ManufUnit;
use App\Models\RetailStock;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\StockUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class StockController extends Controller
{
    public function index()
    {
        $view = view('master.stock');
        $view->stocks = Stock::with(['category', 'parentCategory'])->get();
        return $view;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'integer',
            'parent_category_id' => 'integer',
            'unit_backend' => 'required|string|max:10',
            'unit_default' => 'string|max:20'
        ]);
        $request->merge(['book_journal_id', bookID()]);
        $stock = Stock::findOrFail($id);
        $stock->update($request->only([
            'name',
            'category_id',
            'parent_category_id',
            'unit_backend',
            'unit_default',
            'bbok_journal_id',
        ]));
        return [
            'status' => 1,
            'msg' => $stock
        ];
    }


    public function store(Request $request)
    {
        $request->merge(['book_journal_id', bookID()]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|integer',
                'parent_category_id' => 'required|integer',
                'unit_backend' => 'required|string|max:10',
                'type' => 'required|string|max:10',
            ]);
            $validated['book_journal_id'] = bookID();

            Stock::create($validated);
            return redirect()->back()->with('success', 'Stock berhasil disimpan!');
        } catch (ValidationException $e) {
            $errorString = $e->validator->errors()->first();

            return redirect()->back()
                ->with('error', $errorString); // <- ini isi session 'errors'

        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Stock gagal disimpan! ' . $e->getMessage());
        }
    }

    public function categoryStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'integer',
        ]);

        StockCategory::create($request->only([
            'name',
            'parent_id',
        ]));

        return redirect()->back()->with('success', 'Kategori berhasil disimpan!');
    }

    public function show()
    {
        return "halo halo bandung";
    }
    public function trashed()
    {
        $view = view('master.stock-trashed');
        $view->stocks = Stock::withoutGlobalScope()->trashed()->with(['category', 'parentCategory'])->get();
        return $view;
    }

    public function categoryGetItem()
    {
        $search = getInput('search');
        $searchs = [];
        if ($search)
            $searchs = explode(' ', $search);

        $categories = StockCategory::where('is_deleted', 0);
        foreach ($searchs as $s) {
            $categories = $categories->where('name', 'like', '%' . $s . '%');
        }
        $categories = $categories->select('id', DB::raw('name as text'))->get();

        return ['results' => $categories];
    }

    public function getStockItems()
    {
        $search = getInput('search');
        $searchs = [];
        if ($search)
            $searchs = explode(' ', $search);

        $stocks = Stock::where('is_deleted', 0);
        foreach ($searchs as $s) {
            $stocks = $stocks->where('name', 'like', '%' . $s . '%');
        }
        $stocks = $stocks->select('id', DB::raw('name as text'))->get();

        return ['results' => $stocks];
    }

    public function getInfo($id)
    {
        $stock = Stock::with(['units'])->find($id);
        if (!$stock) {
            return [
                'status' => 0,
                'msg' => 'Stock tidak ditemukan'
            ];
        }
        return [
            'status' => 1,
            'msg' => $stock
        ];
    }
    public function unitStore(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|integer',
            'unit' => 'required|string|max:15', // typo: 'stirng' → 'string'
            'konversi' => 'required|numeric',   // tidak ada rule 'decimal' di Laravel
        ]);

        StockUnit::create($request->only([
            'stock_id',
            'unit',
            'konversi'
        ]));

        return [
            'status' => 1,
            'msg' => StockUnit::where('stock_id', $request->input('stock_id'))->get(),
            'stock' => Stock::find($request->input('stock_id')),
            'hal' => 'Satuan berhasil disimpan!'
        ];
    }


    public function getItem(Request $request)
    {
        $search = $request->get('search');
        $searchs = explode(' ', $search);
        $stocks = Stock::from('stocks');
        foreach ($searchs as $s) {
            $stocks = $stocks->where('name', 'like', '%' . $s . '%');
        }
        $stocks = $stocks->select('id', DB::raw('name as text'))->get();
        return response()->json(['results' => $stocks]);
    }

    function getUnit($id)
    {
        $unit = StockUnit::where('stock_id', $id)->get();
        $stock= Stock::find($id);
        if (!$unit) {
            return [
                'status' => 0,
                'msg' => 'unit tidak ditemukan'
            ];
        }
        return [
            'status' => 1,
            'msg' => $unit,
            'stock'=>$stock,
        ];
    }
    function openSinkron($book_journal_id)
    {
        $book = BookJournal::find($book_journal_id);
        if (!$book) {
            return [
                'status' => 0,
                'msg' => 'Book Journal tidak ditemukan'
            ];
        }
        $defaultDB = config('database.connections.mysql.database');

        $stockModelClass = $book->name == "Buku Toko" ? RetailStock::class : ManufStock::class;

        $stocks = $stockModelClass::from('stocks as rst')
            ->leftJoin($defaultDB . '.stocks as st', function ($join) use ($stockModelClass) {
                $join->on('rst.id', '=', 'st.reference_stock_id')
                    ->where('st.reference_stock_type', '=', $stockModelClass);
            })->where(function ($q) {
                $q->where('st.id', null)->orWhere('st.updated_at', '<', DB::raw('rst.updated_at'));
            })->whereNull('rst.deleted')->where('rst.is_stock',1)->where('rst.is_ppn',1)->with('category:id,name')->with('parentCategory:id,name')
            ->select(
                'rst.name',
                'rst.unit_info as unit_default',
                'rst.unit_backend as unit_backend',
                'rst.category_id',
                'rst.parent_category_id',
                'rst.id',
                'st.id as master_stock_id'
            )->get();
        $stocks = $stockModelClass::withUnits($stocks);

        $view = view('master.modal._link_stock');
        $view->stocks = $stocks;
        return $view;
    }

    public static function sync(Request $request)
    {

        DB::beginTransaction();
        try {
            if ($request->input('book_journal_id')) {
                $bookID = $request->input('book_journal_id');
                $bookModel = $bookID == 1 ? ManufStock::class : RetailStock::class;
            } else
                $bookModel = book()->name == "Buku Toko" ? RetailStock::class : ManufStock::class;
            $datastock = $request->input('data');
            $referenceStockID = $request->input('stock_id');
            $name = $datastock['name'];
            $category_name = $datastock['category']['name'];
            $parent_category_name = $datastock['parent_category']['name'];
            $unit_default = $datastock['unit_default'];
            $unit_backend = $datastock['unit_backend'] ?? 'Pcs';
            $stock_id = $datastock['master_stock_id'];
            $units = $datastock['units_manual'] ?? [];
            $parentcat = StockCategory::addCategoryIfNotExists($parent_category_name);
            $cat = StockCategory::addCategoryIfNotExists($category_name, $parent_category_name);


            $dataFix = [
                'name' => $name,
                'category_id' => $cat->id,
                'parent_category_id' => $parentcat->id,
                'unit_default' => $unit_default,
                'unit_backend' => $unit_backend,
                'book_journal_id' => bookID(),
                'reference_stock_id' => $referenceStockID,
                'reference_stock_type' => $bookModel
            ];

            if ($stock_id) {
                $stock = Stock::find($stock_id);
                $stock->update($dataFix);
            } else {
                $stock = Stock::create($dataFix);
            }

            $stock->refresh();

            //oke kalo sudah waktunya bikin unit ya
            if ($units) {
                $allUnitName = collect($units)->pluck('unit')->toArray();
                foreach ($stock->units as $unit) {
                    if (!in_array($unit->unit, $allUnitName)) {
                        $unit->delete();
                    }
                }


                foreach ($units as $unit) {
                    $nameKolom = book()->name == 'Buku Toko' ? 'retail_stock_id' : 'stock_id';
                    $stunit = StockUnit::where('stock_id', $unit[$nameKolom])->where('unit', $unit['unit'])->first();

                    if ($stunit) {
                        $stunit->update([

                            'konversi' => $unit['konversi']
                        ]);
                    } else {
                        $dataunit = StockUnit::create([
                            'stock_id' => $stock->id,
                            'unit' => $unit['unit'],
                            'konversi' => $unit['konversi']
                        ]);
                    }
                }
            }
            DB::commit();
            return [
                'status' => 1,
                'msg' => $stock->refresh(),
                'request' => $request->all()

            ];
        } catch (Throwable $th) {
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage(),
                'trace' => $th->getTrace(),
                'request' => $request->all()
            ];
        }
    }
}
