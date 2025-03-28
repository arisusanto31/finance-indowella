<?php

use App\Models\BookJournal;
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


if (!function_exists('user')) {
    function user()
    {
        return auth()->user();
    }
}

if (!function_exists('sessionJournal')) {
    function sessionJournal()
    {
        return BookJournal::find(session('book_journal_id'));
    }
}

if (!function_exists('getInput')) {
    function getInput($key)
    {
        if (array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }
    }
}

if (!function_exists('carbonDate')) {
    function carbonDate()
    {
        return new Carbon();
    }
}

function segmentRequest($i)
{
    return FacadeRequest::segment($i);
}
