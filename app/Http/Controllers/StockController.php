<?php

namespace App\Http\Controllers;

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

        $stock = Stock::findOrFail($id);
        $stock->update($request->only([
            'name',
            'category_id',
            'parent_category_id',
            'unit_backend',
            'unit_default'
        ]));

        return [
            'status' => 1,
            'msg' => $stock
        ];
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|integer',
                'parent_category_id' => 'required|integer',
                'unit_backend' => 'required|string|max:10',
            ]);

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

    function getUnit($id){
        $unit = StockUnit::where('stock_id', $id)->get();
        if (!$unit) {
            return [
                'status' => 0,
                'msg' => 'unit tidak ditemukan'
            ];
        }
        return [
            'status' => 1,
            'msg' => $unit
        ];

        
    }
}
