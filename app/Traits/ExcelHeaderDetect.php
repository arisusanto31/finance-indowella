<?php

namespace App\Traits;

trait ExcelHeaderDetect
{
    //


    private function detectHeader(array $rows, array $expected): array
    {
        $maxScan = min(50, count($rows)); // scan 50 baris pertama

        $bestRow = null;
        $bestScore = 0;
        $bestMap = [];

        for ($i = 0; $i < $maxScan; $i++) {
            $row = $rows[$i];

            // bikin map kolom: headerName -> colIndex
            $map = [];
            $score = 0;

            foreach ($row as $colIndex => $cell) {
                // $h = $this->normHeader($cell);
                // if ($h === '') continue;
                $h= $cell;
                // cocokkan dengan expected (bisa exact / contains)
                foreach ($expected as $exp) {
                    if ($h === $exp || str_contains($h, $exp)) {
                        $exp= $this->normHeader($exp);
                        if (!isset($map[$exp])) {
                            $map[$exp] = $colIndex;
                            $score++;
                        }
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestRow = $i;
                $bestMap = $map;
            }
            if( $bestScore === count($expected)) {
                // sudah maksimal, gak usah dilanjut
                break;
            }
        }

        // threshold: minimal cocok 4 header
        return [$bestRow, $bestMap];
    }

    private function normHeader($value): string
    {
        $s = strtolower(trim((string)$value));
        $s = preg_replace('/\s+/', ' ', $s);     // spasi ganda jadi 1
        $s = str_replace(['.', ':'], '', $s);

        // alias biar fleksibel
        $aliases = [
            'harga pcs' => 'harga/pcs',
            'harga per pcs' => 'harga/pcs',
            'qty' => 'quantity',
            'kode' => 'kode barang',
        ];

        return $aliases[$s] ?? $s;
    }

    private function getCell(array $row, ?int $idx)
    {
        if ($idx === null) return null;
        return $row[$idx] ?? null;
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if (trim((string)$v) !== '') return false;
        }
        return true;
    }

    private function toNumber($v): ?float
    {
        if ($v === null) return null;
        // handle "1,640" / "2,050,000.00" / "1.940.000" (kadang beda locale)
        $s = trim((string)$v);

        // kalau format indo pakai titik ribuan:
        // coba: hapus spasi, hapus titik ribuan, koma jadi titik desimal (optional)
        $s = str_replace(' ', '', $s);

        // heuristik: kalau ada "," dan juga "." -> biasanya US (comma ribuan, dot desimal)
        if (str_contains($s, ',') && str_contains($s, '.')) {
            $s = str_replace(',', '', $s);
        } else {
            // kalau cuma ada "." dan panjang setelah "." = 3, kemungkinan ribuan indo
            // (ini heuristik sederhana)
            $s = preg_replace('/\.(?=\d{3}(\D|$))/', '', $s);
            $s = str_replace(',', '.', $s);
        }

        return is_numeric($s) ? (float)$s : null;
    }


    private function buildTwoRowHeader(array $r1, array $r2): array
    {
        // normalize string: lowercase + trim + spasi rapi
        $norm = fn($v) => strtolower(trim(preg_replace('/\s+/', ' ', (string)$v)));

        $headers = [];
        $lastGroup = null;

        $max = max(count($r1), count($r2));
        for ($i = 0; $i < $max; $i++) {
            $top = $norm($r1[$i] ?? '');
            $sub = $norm($r2[$i] ?? '');

            // karena merged: cell di tengah merge biasanya kosong -> pakai group terakhir
            if ($top !== '') $lastGroup = $top;

            // kolom A-D: biasanya cuma ada top header (No, Kode Barang, dst)
            if ($sub === '') {
                $headers[$i] = $lastGroup ?: null;
                continue;
            }

            // kolom E dst: gabung "group + sub"
            $headers[$i] = trim(($lastGroup ?: '') . ' ' . $sub);
        }

        return $headers;
    }

    public function extractData($headers, $isTwoHeader = false)
    {
        [$rowHeader, $mapHeader] = $this->detectHeader($this->array, $headers);
        $dataArray = $this->array;
        $maxColumn = count($dataArray[$rowHeader]);
        $fixRow= $rowHeader;
      
        // return $mapHeader;
        if ($isTwoHeader == true) {
            $fixHeader = [];
            foreach ($mapHeader as $key => $index) {
                $rowSubHeader = $rowHeader + 1;
                for ($i = $index; $i < $maxColumn; $i++) {
                    $thiscell= $this->normHeader($dataArray[$rowHeader][$i]);
                    if ($thiscell != "" && $thiscell != $key) {
                        $i= $maxColumn+1; // break loop
                    } else {
                        $subKey = $this->normHeader($dataArray[$rowSubHeader][$i]);
                        $finalKey = $subKey != "" ? $key . '_' . $subKey : $key;
                        $fixHeader[$finalKey] = $i;
                    }
                }
            }
            $fixRow= $rowHeader+1;
        } else {
            $fixHeader = $mapHeader;
        }

        $allData=[];
        for ($i = $fixRow + 1; $i < count($dataArray); $i++) {
            $row = $dataArray[$i];
            if ($this->isRowEmpty($row)) continue;

            $item = [];
            foreach ($fixHeader as $key => $colIndex) {
                $thekey = str_replace(' ', '_', mb_strtolower($key));
                $item[$thekey] = $this->getCell($row, $colIndex);
            }
            $allData[] = $item;
        }
        return $allData;
    }
}
