<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokoController extends Controller
{
    //
    public function showDeleted()
    {
        $deletedSuppliers = Toko::withoutGlobalScope('aktif')->where('is_deleted', 1)
            ->get();


        //  dd($deletedSuppliers);

        return view('master.deleted_supplier', compact('deletedSuppliers'));
    }


    public function restore($id)
    {
        $supplier = Toko::withoutGlobalScope('aktif')->findOrFail($id);
        $supplier->is_deleted = 0;
        // $supplier->deleted_at = null;
        $supplier->save();

        return redirect()->route('supplier.main.deleted')->with('success', 'Supplier berhasil dipulihkan!');
    }


    public function softDelete($id)
    {
        $supplier = Toko::findOrFail($id);
        $supplier->update([
            'is_deleted' => 1,
            'deleted_at' => now()
        ]);
        return redirect()->route('supplier.main.index')
            ->with('success', 'Supplier berhasil disembunyikan!');
    }

    public function edit($id)
    {
        $toko = Toko::findOrFail($id);
        return view('master.modal._edit_toko', compact('toko'));
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'book_journal_id' => session('book_journal_id'),
        ]);
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'nullable',
            'book_journal_id' => 'required|integer'
        ]);
        $toko = Toko::findOrFail($id);
        $toko->update($data);
        return redirect()->route('toko.main.index')->with('success', 'data toko berhasil diperbarui!');
    }

    public function index()
    {
        $tokoes = Toko::all();
        return view('master.toko', compact('tokoes'));
    }

    public function create()
    {
        return view('master.modal._create_toko');
    }

    public function store(Request $request)
    {
        $request->merge([
            'book_journal_id' => session('book_journal_id'),
        ]);
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'nullable',
            'book_journal_id' => 'required|integer'
        ]);
        $toko = Toko::create($data);
        return redirect()
            ->route('toko.main.index')
            ->with('success', 'Toko berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $supplier = Toko::findOrFail($id);
        $supplier->update([
            'is_deleted' => 1,
            'deleted_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Toko berhasil dinonaktifkan!');
    }



    public function getItem()
    {
        $searchs = explode(' ', request('search'));
        $supps = Toko::query();
        foreach ($searchs as $s) {
            $supps->where('name', 'like', "%$s%");
        }
        $supps = $supps->select('id', DB::raw('name as text'))->get();
        return ['results' => $supps];
    }
}
