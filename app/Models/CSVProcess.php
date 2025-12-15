<?php

namespace App\Models;

use App\Helpers\FileHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use RuntimeException;
use SplFileObject;

class CSVProcess extends Model
{
    use HasFactory;


    public static function upload($file, $path, $name = "")
    {
        if ($file) {
            if ($name == "")
                $filename = date("YmdHis") . '-' . str_replace("&", "n", $file->getClientOriginalName());
            else {
                $filename = $name;
            }
            if ($file->move($path, $filename)) {
                return $path . '/' . $filename;
            }
            return null;
        }
        return null;
    }

    public static function parseStockCsv(Request $request)
    {

        $file = $request->file('csv_file');
        $keys = $request->input('keys', []);


        $adr = self::upload($file, 'assets', '_tmp.csv');
        $delimiter = $request->input('delimiter', ',');

        $path = public_path(ltrim($adr, ','));
        if (!is_file($path)) {
            throw new RuntimeException("File tidak ditemukan: $path");
        }

        $file = new SplFileObject($path, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($delimiter);


        $headers = null;
        while (!$file->eof()) {
            $row = $file->fgetcsv();
            if ($row === false || $row === [null]) continue;

            // Hilangkan BOM di kolom pertama
            if (isset($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$row[0]);
            }

            // Normalizer: trim, lower, satukan spasi, hilangkan NBSP
            $norm = function ($s) {
                $s = (string)$s;
                $s = str_replace("\xC2\xA0", ' ', $s);            // U+00A0 NBSP
                $s = preg_replace('/\s+/u', ' ', $s);             // semua whitespace → satu spasi
                $s = trim($s);
                return mb_strtolower($s, 'UTF-8');
            };

            $headers = array_map($norm, $row);
            break;
        }
        if (!$headers) return [];


        // Normalisasi $keys juga!
        $normKeys = array_map($norm, $keys);


        // Buat peta header → index, lalu ambil index untuk setiap key
        $map = array_flip($headers); // ['kode barang' => 0, 'saldo quantity' => 1, ...]
        // return $map;
        $idxs = [];
        foreach ($normKeys as $i => $k) {
            $idxs[$keys[$i]] = $map[$k] ?? false; // simpan pakai nama aslinya biar enak dipakai
        }

        // DEBUG sementara kalau perlu:
        // return ['headers'=>$headers,'keys_asli'=>$keys,'keys_norm'=>$normKeys,'idxs'=>$idxs];

        // --- Baca baris data ---
        $out = [];
        while (!$file->eof()) {
            $row = $file->fgetcsv();
            if ($row === false || $row === [null]) continue;

            $baris = [];
            foreach ($idxs as $key => $idx) {
                $thekey = str_replace(' ', '_', mb_strtolower($key));
                $baris[$thekey] = ($idx !== false) ? ($row[$idx] ?? null) : null;
            }
            $out[] = $baris;
        }
        return $out;
    }

    public static function parseFromPath($path, $keys, $delimiter = ',')
    {
        if (!is_file($path)) {
            throw new RuntimeException("File tidak ditemukan: $path");
        }

        $file = new SplFileObject($path, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($delimiter);


        $headers = null;
        while (!$file->eof()) {
            $row = $file->fgetcsv();
            if ($row === false || $row === [null]) continue;

            // Hilangkan BOM di kolom pertama
            if (isset($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$row[0]);
            }

            // Normalizer: trim, lower, satukan spasi, hilangkan NBSP
            $norm = function ($s) {
                $s = (string)$s;
                $s = str_replace("\xC2\xA0", ' ', $s);            // U+00A0 NBSP
                $s = preg_replace('/\s+/u', ' ', $s);             // semua whitespace → satu spasi
                $s = trim($s);
                return mb_strtolower($s, 'UTF-8');
            };

            $headers = array_map($norm, $row);
            break;
        }
        if (!$headers) return [];


        // Normalisasi $keys juga!
        $normKeys = array_map($norm, $keys);


        // Buat peta header → index, lalu ambil index untuk setiap key
        $map = array_flip($headers); // ['kode barang' => 0, 'saldo quantity' => 1, ...]
        // return $map;
        $idxs = [];
        foreach ($normKeys as $i => $k) {
            $idxs[$keys[$i]] = $map[$k] ?? false; // simpan pakai nama aslinya biar enak dipakai
        }

        // DEBUG sementara kalau perlu:
        // return ['headers'=>$headers,'keys_asli'=>$keys,'keys_norm'=>$normKeys,'idxs'=>$idxs];

        // --- Baca baris data ---
        $out = [];
        while (!$file->eof()) {
            $row = $file->fgetcsv();
            if ($row === false || $row === [null]) continue;

            $baris = [];
            foreach ($idxs as $key => $idx) {
                $thekey = str_replace(' ', '_', mb_strtolower($key));
                $baris[$thekey] = ($idx !== false) ? ($row[$idx] ?? null) : null;
            }
            $out[] = $baris;
        }
        return $out;
    }
}
