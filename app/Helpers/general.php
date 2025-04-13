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

function getListMonth(){
    return [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];
}

function getProsen($data,$total){
    if($total==0){
        return 0;
    }else{
        return round(($data/$total)*100,2);
    }
}
function getProsenDecimal($data,$total){
    if($total==0){
        return 0;
    }else{
        return ($data/$total)*100;
    }
}
if (!function_exists('format_price')) {
    /**
     * @param $value
     * @param $length
     * @return string
     */
    function format_price($number, $decimal = 2,$language="eng")
    {
        try {
            if($language=="eng"){
                return number_format($number, $decimal, ',', ',');
            }else{    
                return number_format($number, $decimal, ',', '.');;
            }   
        } catch (\Exception $e) {
            return "";
        }
    }
}

if (!function_exists('format_db')) {
    /**
     * Mengubah string harga terformat ke format angka standar (float string)
     * Contoh: "1.234.567,89" → "1234567.89"
     * @param string $formatted
     * @param string $language
     * @return string
     */
    function format_db($formatted, $language = 'id')
    {
        try {
            // Hilangkan spasi dan non-digit selain pemisah
            $formatted = trim($formatted);

            if ($language == 'eng') {
                // Format Inggris: 1,234,567.89 → 1234567.89
                $clean = str_replace(',', '', $formatted);
                return (string) $clean;
            } else {
                // Format Indonesia: 1.234.567,89 → 1234567.89
                $clean = str_replace('.', '', $formatted);      // Hilangkan pemisah ribuan
                $clean = str_replace(',', '.', $clean);         // Ganti koma (desimal) jadi titik
                return (string) $clean;
            }
        } catch (\Exception $e) {
            return '0';
        }
    }
}

function getErrorValidation($e){
    return 'kolom: '.implode(',',collect($e->errors())->keys()->all()).' tidak valid';
}

function format_date_db($date){
    $dateDb = Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
    return $dateDb;
}
if (!function_exists('money')) {
    function money($value): string
    {
        return number_format((float)$value, 2, '.', '');
    }
}

if (!function_exists('moneyAdd')) {
    function moneyAdd(string $a, string $b): string
    {
        return bcadd(money($a), money($b), 2);
    }
}

if (!function_exists('moneySub')) {
    function moneySub(string $a, string $b): string
    {
        return bcsub(money($a), money($b), 2);
    }
}

if (!function_exists('moneyMul')) {
    function moneyMul(string $a, string $b): string
    {
        return bcmul(money($a), money($b), 2);
    }
}

if (!function_exists('moneyAbs')) {
    function moneyAbs(string $value): string
    {
        return bccomp($value, '0', 2) < 0 ? moneyMul($value, '-1') : money($value);
    }
}

if (!function_exists('moneyCmp')) {
    function moneyCmp(string $a, string $b = '0'): int
    {
        return bccomp(money($a), money($b), 2);
    }
}


