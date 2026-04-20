<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookTheme extends Model
{
    //

    protected $table = "user_book_themes";
    protected $fillable = [
        'user_id',
        'book_id',
        'theme'
    ];

    public static function createOrUpdate($userId, $bookId, $theme)
    {
        try {
            self::updateOrCreate(
                ['user_id' => $userId, 'book_id' => $bookId],
                ['theme' => $theme]
            );
        } catch (\Exception $e) {
            return ['status' => 0, 'msg' => 'gagal menyimpan tema'];
        }
        return ['status' => 1, 'msg' => 'tema berhasil diubah'];
    }
}
