<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FinalReport extends Model
{
    //
    protected $table = "final_reports";
    public $timestamps = true;

    protected $fillable = [
        'book_journal_id',
        'month',
        'year',
        'file_path',
        'key_file'
    ];

    public static function createData(Request $request)
    {

        try {
            $data = $request->validate([
                'book_journal_id' => 'required|integer',
                'month' => 'required|integer',
                'year' => 'required|integer',
                'file_path' => 'required|string',
                'key_file' => 'nullable|string'
            ]);

            if (!array_key_exists('key_file', $data) || $data['key_file'] == null) {
                $data['key_file'] = $data['book_journal_id'] . '-' . $data['month'] . '-' . $data['year'];
            }

            //yang lama dihapus aja ya lur
            $existing = self::where('key_file', $data['key_file'])->where('book_journal_id', $data['book_journal_id'])->first();
            if ($existing) {
                if ($existing->file_path != $data['file_path']) {
                    //kita hapus yang ada di server
                    if (file_exists($existing->file_path)) {
                        unlink($existing->file_path);
                    }
                }
                $existing->delete();
            }
            return self::create(
                [
                    'book_journal_id' => $data['book_journal_id'],
                    'month' => $data['month'],
                    'year' => $data['year'],
                    'file_path' => $data['file_path'],
                    'key_file' => $data['key_file'] ?? null
                ]
            );
        } catch (\Exception $e) {
            throw new \Exception('error while creating file export ' . $e->getMessage());
        }
    }
}
