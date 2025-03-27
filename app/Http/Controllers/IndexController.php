<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        return redirect('admin/dashboard');
    }
    public function dashboard()
    {
        return view('dashboard'); 
    }
  
   
}
