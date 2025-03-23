<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Request as FacadeRequest;

if (! function_exists('createCarbon')) {
    function createCarbon($date)
    {
        return new Carbon($date);
    }
}


if (! function_exists('dayInMonthQuantity')) {

    function dayInMonthQuantity($bulan, $tahun)
    {
        $date = createCarbon($tahun . '-' . $bulan . '-01');
        return $date->format('t');
    }
}


if(!function_exists('user')){
    function user(){
        return auth()->user();
    }
}

function segmentRequest($i){
    return FacadeRequest::segment($i);
}