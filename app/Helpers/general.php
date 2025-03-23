<?php

use Carbon\Carbon;

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
