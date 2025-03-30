<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    //
    public function index() {}

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
