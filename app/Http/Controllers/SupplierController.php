<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SupplierController extends Controller 
{
    public function index()
    {
        // dd('INDEX KEPANGGIL');
        $suppliers = Supplier::all();
        return view('master.supplier', compact('suppliers'));
    }
    

    public function getItem()
    {
        $searchs = explode(' ', getInput('search'));
        $supps = Supplier::from('suppliers');
        foreach ($searchs as $s) {
            $supps->where('name', 'like', '%' . $s . '%');
        }
        $supps = $supps->select('id', DB::raw('name as text'))->get();
        return [
            'results' => $supps
        ];
    }

 

 
 public function store(Request $request)
 {
    
     $request->validate([
         'name' => 'required|unique:suppliers,name',
         'npwp' => 'required',
         'ktp' => 'required',
         'cp_name' => 'required',
         'phone' => 'required',
         'address' => 'nullable',
     ]);

     
     Supplier::create([
         'name' => $request->name,
         'npwp' => $request->npwp,
         'ktp' => $request->ktp,
         'cp_name' => $request->cp_name,
         'phone' => $request->phone,
         'address' => $request->address,
         'is_deleted' => false, // default false
     ]);
     return redirect()->back()->with('success', 'Customer berhasil disimpan!');

    //  return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan!');
 }



    public function create(){
        $view = view('kartu.modal._supplier');
        return $view;
    }


    public function destroy($id){
        $supplier= Supplier::find($id);
        $supplier->is_deleted=1;
        $supplier->deleted_at= Date('Y-m-d H:i:s');
        $supplier->save();
        return [
            'status'=>1,'msg'=>'success delete'
        ];
    }
}
 