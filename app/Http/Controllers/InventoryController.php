<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\KartuInventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    //

    public function index()
    {
        return view('daftar.daftar-at');
    }
    public function store(Request $request)
    {
        $request = $request->validate([
            'name' => 'required|string',
            'keterangan_qty_unit' => 'string',
            'date' => 'required|date',
            'nilai_perolehan' => 'required|numeric',
            'periode' => 'required|integer'
        ]);

        $inv = Inventory::create($request);
        return [
            'status' => 1,
            'msg' => $inv
        ];
    }
    public function update(Request $request){
        $request = $request->validate([
            'name' => 'required|string',
            'keterangan_qty_unit' => 'string',
            'date' => 'required|date',
            'nilai_perolehan' => 'required|numeric',
            'periode' => 'required|integer'
        ]);

        $inv = Inventory::update($request);
        return [
            'status' => 1,
            'msg' => $inv
        ];
    }

    public function storeKartu(Request $request){
        KartuInventory::createKartu($request);
    }
}
