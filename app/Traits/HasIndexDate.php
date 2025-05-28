<?php

namespace App\Traits;

use App\Models\JournalKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait HasIndexDate
{

    public static function bootHasIndexDate()
    {
        static::creating(function ($model) {
            $newdate = Carbon::createFromFormat('ymdHis', $model->index_date_group);
            $model->created_at = $newdate;
        });

        static::updating(function ($model) {
            $newdate = Carbon::createFromFormat('ymdHis', $model->index_date_group);
            $model->created_at = $newdate;
        });
    }
    public static function getNextIndexDate($inputDate)
    {
        $date = createCarbon($inputDate)->format('ymdHis');

        $lastData = static::query()->where('index_date_group', $date)
            ->select(DB::raw('MAX(index_date) as maxindex'))
            ->first();

        $lastIndex = $lastData && $lastData->maxindex ? ((int) substr($lastData->maxindex, -3)) : 0;

        $newIndex = $date . str_pad($lastIndex + 1, 3, '0', STR_PAD_LEFT);

        return $newIndex;
    }

    public static function proteksiBackdate($date)
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

    public static function isBackdate($date)
    {
        return carbonDate()->subMinutes(5) > createCarbon($date);
    }
}
