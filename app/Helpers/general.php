<?php

use App\Models\BookJournal;
use App\Services\ContextService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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

if (!function_exists('book')) {
    function book()
    {
        return BookJournal::find(bookID());
    }
}

if (!function_exists('bookID')) {
    function bookID()
    {
        $bookJournalID = ContextService::getBookJournalID();
        return $bookJournalID;
    }
}

if (!function_exists('db_date_from_dmy')) {
    function db_date_from_dmy(?string $dmy): ?string
    {
        if (!$dmy) return null;

        $dmy = trim($dmy);

        $dt = \DateTime::createFromFormat('d/m/Y', $dmy);
        $errors = \DateTime::getLastErrors();

        if (!$dt || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            return null; // atau throw exception kalau kamu mau strict
        }

        return $dt->format('Y-m-d');
    }
}

if (!function_exists('norm_string')) {

    function norm_string($value)
    {
        $s = strtolower(trim((string)$value));
        $s = preg_replace('/\s+/', ' ', $s);     // spasi ganda jadi 1
        $s = str_replace(['.', ':'], '', $s);
        $s = str_replace(['/', ' '], '_', $s);
        return $s;
    }
}

class CustomLogger
{
    public static function log($title, $level, $message, $context = [])
    {
        try {
            Log::channel($title)->$level($message, $context);
        } catch (Throwable $th) {
        }
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

function getListMonth()
{
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

function getListMonthYear()
{
    $date = carbonDate();
    $listMonthYear = [];
    for ($i = 0; $i < 12; $i++) {
        $thedate = $date->copy()->subMonth($i);
        $listMonthYear[] = $thedate->format('Y-m');
    }
    return $listMonthYear;
}

function getListYear()
{
    $date = carbonDate()->format('Y');
    $listYear = [];
    for ($i = 0; $i < 5; $i++) {
        $thedate = $date - $i;
        $listYear[] = $thedate;
    }
    return $listYear;
}



function getProsen($data, $total)
{
    if ($total == 0) {
        return 0;
    } else {
        return round(($data / $total) * 100, 2);
    }
}
function getProsenDecimal($data, $total)
{
    if ($total == 0) {
        return 0;
    } else {
        return ($data / $total) * 100;
    }
}

function getModel($model)
{
    $model = str_replace('App\\Models\\', '', $model);
    $model = str_replace('\\', '_', $model);
    return $model;
}
if (!function_exists('format_price')) {
    /**
     * @param $value
     * @param $length
     * @return string
     */
    function format_price($number, $decimal = 2, $language = "id")
    {
        try {
            if ($language == "eng") {
                return number_format($number, $decimal, ',', ',');
            } else {
                return number_format($number, $decimal, ',', '.');;
            }
        } catch (\Exception $e) {
            return "";
        }
    }
}

function detectFormat($input)
{
    $input = trim($input);

    // Format database: -123.45 atau 1000.5
    $dbPattern = '/^-?\d+(\.\d+)?$/';

    // Format rupiah: 1.000.000,50 atau 123,45 atau 100.000
    $rupiahPattern = '/^(\d{1,3}(\.\d{3})*|\d+)(,\d{1,2})?$/';

    if (preg_match($rupiahPattern, $input)) {
        return 'rupiah';
    } elseif (preg_match($dbPattern, $input)) {
        return 'database';
    } else {
        return 'unknown';
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
            //parah se disini ternyata inputnya harus benar2 string dengan format indo
            //kalo ternyata ini adalah format db yang ada komanya, hancur udah datanya.

            // Cek format input
            $format = detectFormat($formatted);
            if ($format == 'database') {
                info('database:' . $formatted);
                return (string) $formatted;
            }

            // Hilangkan spasi dan non-digit selain pemisah
            $formatted = trim($formatted);

            if ($language == 'eng') {
                // Format Inggris: 1,234,567.89 → 1234567.89
                $clean = str_replace(',', '', $formatted);
                return (string) $clean;
            } else {
                // Format Indonesia: 1.234.567,89 → 1234567.89
                //harus dicek dulu , kalau sudah integer g usah, kalau string baru di proses nih
                $formatted = strval($formatted);
                $formatted = str_replace(' ', '', $formatted); // Hilangkan spasi
                $clean = str_replace('.', '', $formatted);      // Hilangkan pemisah ribuan
                $clean = str_replace(',', '.', $clean);         // Ganti koma (desimal) jadi titik
                return (string) $clean;
            }
        } catch (\Exception $e) {
            return '0';
        }
    }
}

function getErrorValidation($e)
{
    return 'kolom: ' . implode(',', collect($e->errors())->keys()->all()) . ' tidak valid';
}

function format_date_db($date)
{
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


function toDigit($number, $digit)
{
    $str = sprintf("%0" . $digit . "d", $number);
    return $str;
}

function ownucfirst($string)
{
    $lower = strtolower(trim($string));
    return ucfirst($lower);
}

function bgStatus($text)
{
    if ($text == "success") {
        return "bg-success";
    } else if ($text == "failed") {
        return "bg-danger";
    } else if ($text == "process") {
        return "bg-warning";
    } else {
        return "bglevel3";
    }
}
