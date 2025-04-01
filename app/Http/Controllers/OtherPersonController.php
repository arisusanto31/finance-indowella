<?php

namespace App\Http\Controllers;

use App\Models\OtherPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;




class OtherPersonController extends Controller
{
    //
    public function index() {}

    public function getItem()
    {
        $searchs = explode(' ', getInput('search'));
        $supps = OtherPerson::from('other_persons');
        foreach ($searchs as $s) {
            $supps->where('name', 'like', '%' . $s . '%');
        }
        $supps = $supps->select('id', DB::raw('name as text'))->get();
        return [
            'results' => $supps
        ];
    }

    public function destroy($id)
    {
        $supplier = OtherPerson::find($id);
        $supplier->is_deleted = 1;
        $supplier->deleted_at = Date('Y-m-d H:i:s');
        $supplier->save();
        return [
            'status' => 1,
            'msg' => 'success delete'
        ];
    }

    public function trashed()
    {
        $trashed = OtherPerson::onlyTrashed()->get();
        return view('other_persons.trashed', compact('trashed'));
    }

    // Memulihkan data yang di-soft delete
    public function restore($id)
    {
        $person = OtherPerson::withTrashed()->findOrFail($id);
        $person->update(['is_deleted' => 0]); // Optional, jika kamu pakai kolom is_deleted
        $person->restore();

        return redirect()->route('other-persons.index')->with('success', 'Data berhasil dipulihkan.');
    }
}
