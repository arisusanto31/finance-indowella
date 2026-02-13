<?php

namespace App\Http\Controllers;

use App\Models\ChartAccount;
use App\Models\LinkTokoParent;
use App\Models\ManufToko;
use App\Models\RetailToko;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'book_journal_id' => bookID(),
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
        $codeGroups = ChartAccount::withAlias()->where('chart_accounts.code_group', 'like', '1110%')
            ->where('chart_accounts.code_group', '>', 111000)
            ->select('ca.code_group', 'ca.name')->get();
        return view('master.toko', compact('tokoes', 'codeGroups'));
    }

    public function create()
    {
        return view('master.modal._create_toko');
    }

    public function store(Request $request)
    {
        $request->merge([
            'book_journal_id' => bookID(),
        ]);
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'nullable',
            'kode_toko'=>'nullable',
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

    public function getParentOption()
    {
        $parentType = getInput('parent_type');
        if ($parentType == 'retail') {
            $search = explode(' ', getInput('search'));
            $query = \App\Models\RetailToko::query();
            foreach ($search as $s) {
                $query->where('name', 'like', "%$s%");
            }
            $results = $query->select('id', 'name as text')->get();
        } else {
            $results = [
                ['id' => 0, 'text' => 'Tidak Ada']
            ];
        }
        return ['results' => $results];
    }

    public function makeLinkParent(Request $request)
    {
        try {
            $tokoid = $request->input('toko_id');
            $parentID = $request->input('parent_id');
            $parentType = $request->input('parent_type');
            if ($parentType == 'retail') {
                $theParentType = RetailToko::class;
            } else if ($parentType == 'manuf') {
                $theParentType = ManufToko::class;
            } else {
                $theParentType = null;
            }

            $link = LinkTokoParent::where('book_journal_id', bookID())
                ->where('parent_id', $parentID)
                ->first();
            if (!$link) {
                $link = new LinkTokoParent;
            }
            $link->book_journal_id = bookID();
            $link->parent_id = $parentID;
            $link->parent_type = $theParentType;
            $link->toko_id = $tokoid;
            $link->save();

            return [
                'status' => 1,
                'msg' => $link
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    function changeCodeKas(Request $request)
    {

        try {
            $request->validate([
                'toko_id' => 'required|integer|exists:tokoes,id',
                'code_group_kas' => 'required|string|exists:chart_accounts,code_group',
            ]);

            $toko = Toko::findOrFail($request->input('toko_id'));
            $toko->default_code_group_kas = $request->input('code_group_kas');
            $toko->save();

            return response()->json([
                'status' => 1,
                'msg' => $toko
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'status' => 0,
                'msg' => $ve->errors()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => $e->getMessage()
            ]);
        }
    }
}
