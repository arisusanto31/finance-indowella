<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use Carbon\Carbon;

class KaryawanController extends Controller
{
    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->delete();

        return redirect()->back()->with('success', 'Data karyawan berhasil dihapus!');
    }


    public function edit($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return view('daftar.modal._edit_karyawan', compact('karyawan'));
    }

    public function show($id)
{
    return redirect()->route('karyawan.index');
}

    public function softDelete($id)
    {
        try {
            $karyawan = Karyawan::withoutGlobalScopes()->findOrFail($id);
            $karyawan->is_deleted = 1;
            $karyawan->save();
    
            return redirect()
                ->route('karyawan.index') 
                ->with('success', 'Karyawan berhasil di delete lur!');
        } catch (\Throwable $e) {
            return redirect()
                ->route('karyawan.index')
                ->with('error', 'Gagal soft delete: ' . $e->getMessage());
        }
    }
    

    public function resign($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update([
            'date_keluar' => Carbon::now()->toDateString()
        ]);

        return redirect()->back()->with('success', 'Karyawan berhasil diresign.');
    }

  

    // public function DaftarKaryawan()
    // {
    //     return view('daftar.daftar-karyawan');
    // }

    public function create()
    {
        return view('daftar.modal._create_karyawan');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|max:255',
            'npwp' => 'nullable|string|max:255',
            'jabatan' => 'required|string|max:255',
            'date_masuk' => 'required|date',
            'date_keluar' => 'nullable|date',
        ]);

        $validated['book_journal_id'] = bookID();
        $karyawan = Karyawan::create($validated);

        $status = is_null($karyawan->date_keluar) ? 'Aktif' : 'Keluar';


        return redirect()->back()->with('success', 'Karyawan berhasil disimpan!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'nik' => 'required',
            'npwp' => 'nullable',
            'jabatan' => 'required',
           
        ]);
    
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update($validated);
    
        return redirect()->back()->with('success', 'Data berhasil diupdate!');
        // dd('Masuk ke update!', $request->all());
    }
    
    public function deleted()
    {
        $karyawans = Karyawan::withoutGlobalScopes()
            ->where('is_deleted', 1)
            ->get();
        // dd($karyawans);
    
        return view('daftar.delete-karyawan', compact('karyawans'));
       

    }
    
    


    public function index()
    {
        // dd(bookID());
        
        $karyawans = Karyawan::where('is_deleted', 0)->get();
        $karyawans = Karyawan::all();
        // dd(Karyawan::where('is_deleted', 0)->pluck('nama'));

        return view('daftar.daftar-karyawan', compact('karyawans'));
    }
}
