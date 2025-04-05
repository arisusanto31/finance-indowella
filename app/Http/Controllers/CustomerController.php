<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function restoreAll()
    {
        Customer::withoutGlobalScope('customer')
            ->where('is_deleted', 1)
            ->update([
                'is_deleted' => null,
                'deleted_at' => null,
            ]);

        return redirect()->route('customers.trashed')->with('success', 'Semua customer berhasil dipulihkan.');
    }


    public function restore($id)
    {
        $customer = Customer::withoutGlobalScope('customer')->findOrFail($id);
        $customer->is_deleted = null;
        $customer->deleted_at = null;
        $customer->save();

        return redirect()->route('customers.trashed')->with('success', 'Customer berhasil dipulihkan.');
    }

    public function index()
    {
        $customers = Customer::all(); // Ini otomatis pakai global scope jika ada
        return view('master.customer', compact('customers'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'ktp' => 'nullable|string|max:100',
            'npwp' => 'nullable|string|max:100',
        ]);

        Customer::create($request->only([
            'name',
            'address',
            'phone',
            'ktp',
            'npwp',
        ]));

        return redirect()->back()->with('success', 'Customer berhasil disimpan!');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->is_deleted = 1;
        $customer->deleted_at = now();
        $customer->save();

        return redirect()->back()->with('success', 'Customer berhasil dihapus.');
    }

    public function trashed()
    {
        $customers = Customer::withoutGlobalScope('customer')
            ->where('is_deleted', 1)
            ->get();

        return view('master.customer-trashed', compact('customers'));
    }
    public function edit($id)
    {
        $customer = Customer::withoutGlobalScope('customer')->findOrFail($id);
        return view('master.customer-edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'ktp' => 'nullable|string|max:100',
            'npwp' => 'nullable|string|max:100',
        ]);

        $customer = Customer::withoutGlobalScope('customer')->findOrFail($id);
        $customer->update($request->only([
            'name',
            'address',
            'phone',
            'ktp',
            'npwp',
        ]));

        return redirect()->route('customer.index')->with('success', 'Customer berhasil diperbarui!');
    }
} 

// class CustomerController extends Controller
// {
//     //
// }
