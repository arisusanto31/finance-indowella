<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class SupplierController extends Controller
{

    public function showDeleted()
    {
        $deletedSuppliers = Supplier::withoutGlobalScope('supplier')
            ->where('is_deleted', 1)
            ->get();


        //  dd($deletedSuppliers);

        return view('master.deleted_supplier', compact('deletedSuppliers'));
    }


    public function restore($id)
    {
        $supplier = Supplier::withoutGlobalScope('supplier')->findOrFail($id);
        $supplier->is_deleted = 0;
        // $supplier->deleted_at = null;
        $supplier->save();

        return redirect()->route('supplier.main.deleted')->with('success', 'Supplier berhasil dipulihkan!');
    }






    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('kartu.modal.edit_supplier', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'npwp' => 'required',
            'ktp' => 'required',
            'cp_name' => 'required',
            'phone' => 'required',
            'address' => 'nullable',
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);

        return redirect()->route('supplier.main.index')->with('success', 'Supplier berhasil diperbarui!');
    }

    public function index()
    {
        $suppliers = Supplier::all();
        return view('master.supplier', compact('suppliers'));
    }

    public function create()
    {
        return view('kartu.modal._supplier');
    }

    public function store(Request $request)
    {
        try {
        $data = $request->validate([
            'name'     => 'required|unique:suppliers,name',
            'npwp'     => 'required',
            'ktp'      => 'required|digits:16',
            'cp_name'  => 'required',
            'phone'    => 'required',
            'address'  => 'nullable',
        ]);
        Supplier::create($data);
        return redirect()
            ->route('supplier.main.index')
            ->with('success', 'Supplier berhasil ditambahkan!');
        } catch (ValidationException $e) {
            return redirect()
                ->route('supplier.main.index')
                ->with('error', 'Supplier gagal ditambahkan! ' . getErrorValidation($e));
        } catch (Throwable $e) {
            return redirect()
                ->route('supplier.main.index')
                ->with('error', 'Supplier gagal ditambahkan! ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'is_deleted' => 1,
            'deleted_at' => now(),
        ]);
        return ['status' => 1, 'msg' => 'supplier berhasil '];
    }



    public function getItem()
    {
        $searchs = explode(' ', request('search'));
        $supps = Supplier::query();

        foreach ($searchs as $s) {
            $supps->where('name', 'like', "%$s%");
        }

        $supps = $supps->select('id', DB::raw('name as text'))->get();

        return ['results' => $supps];
    }
}
