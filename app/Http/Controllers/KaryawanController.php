<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;

class KaryawanController extends Controller
{
    public function DaftarKaryawan()
    {
        return view('daftar.daftar-karyawan');
    }

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
    
        $karyawan = Karyawan::create($validated); 
    
        $status = is_null($karyawan->date_keluar) ? 'Aktif' : 'Keluar';
    
     
        return redirect()->back()->with('success', 'Karyawan berhasil disimpan!');
    }
    public function index()
    {
        $karyawans = Karyawan::all();
        return view('daftar.daftar-karyawan', compact('karyawans'));
    }
}


