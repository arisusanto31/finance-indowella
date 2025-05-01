<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

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

        return redirect()->route('customer.trashed')->with('success', 'Customer berhasil dipulihkan!');

    }

    public function index()
    {
        $customers = \App\Models\Customer::all(); 
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

    public function destroy(Customer $main)
    {
        $main->is_deleted = 1;
        $main->deleted_at = now();
        $main->save();
    
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

        return redirect()->route('customer.main.index')->with('success', 'Customer berhasil diperbarui!');



    }

    public function getItem()
    {
        $searchs = explode(' ', request('search'));
        $cust = Customer::query();

        foreach ($searchs as $s) {
            $cust->where('name', 'like', "%$s%");
        }

        $cust = $cust->select('id', DB::raw('name as text'))->get();

        return ['results' => $cust];
    }


    public function show($id)
{
    $customer = Customer::findOrFail($id);

    return view('master.customer-edit', compact('customer'));

}

} 



// class CustomerController extends Controller
// {
//     //
// }
