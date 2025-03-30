<?php

namespace App\Http\Controllers;

use App\Models\OtherPerson;
use Illuminate\Http\Request;

class OtherPersonController extends Controller
{
    // Menampilkan data yang di-soft delete
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
