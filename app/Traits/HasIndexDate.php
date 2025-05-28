<?php

namespace App\Traits;

use App\Models\JournalKey;
use Illuminate\Support\Facades\DB;

trait HasIndexDate
{
    public function getNextIndexDate($inputDate)
    {
        $date = createCarbon($inputDate)->format('YmdHis');

        $lastData = $this->where('index_date_group', $date)
            ->select(DB::raw('MAX(index_date) as maxindex'))
            ->first();

        $lastIndex = $lastData && $lastData->maxindex ? ((int) substr($lastData->maxindex, -3)) : 0;

        $newIndex = $date . str_pad($lastIndex + 1, 3, '0', STR_PAD_LEFT);

        return $newIndex;
    }

    public function proteksiBackdate($date)
    {
        if (carbonDate()->subMinutes(5) > createCarbon($date)) {
            // ini kan lebih dari 5 menit , jadi ini backdate
            $key = JournalKey::where('book_journal_id', bookID())
                ->where('key_at', '>', $date)->first();
            if ($key) {
                //brati disini sudah ada key yang lebih baru dari tanggal ini
                throw new \Exception('Backdate tidak boleh, coba cek kunci jurnal ');
            }
        }
    }

    public function isBackdate($date)
    {
        return carbonDate()->subMinutes(5) > createCarbon($date);
    }

   
}
