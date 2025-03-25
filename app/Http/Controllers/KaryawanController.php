<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function DaftarKaryawan()
    {
        return view('daftar.daftar-karyawan');
    }
}
