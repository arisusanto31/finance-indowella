<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        return view('login.index');
    }
    public function dashboard()
    {
        return view('dashboard'); 
    }
    public function loginDashboard(){
        return view('login.dashboard');
    }
}
