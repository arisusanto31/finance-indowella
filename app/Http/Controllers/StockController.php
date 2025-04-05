<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockCategory;
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

        ]);

        $stock = Stock::findOrFail($id);
        $stock->update($request->only([
            'name',
            'address',
            'phone',
            'ktp',
            'npwp',
            'purchase_info',
        ]));

        return redirect()->back()->with('success', 'Stock berhasil diperbarui!');
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|integer',
                'parent_category_id' => 'required|integer',
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
}
