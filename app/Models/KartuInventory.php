<?php

namespace App\Models;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class KartuInventory extends Model
{
    //
    protected $table = "kartu_inventories";
    public $timestamps = true;
    public $fillable = [
        'inventory_id',
        'amount',
        'type_mutasi',
        'nilai_buku'
    ];

    public static function createKartu(Request $request)
    {
        $invID = $request->input('inventory_id');
        $lock = Cache::lock('inventory-' . $invID, 30);
        //disini kita ambil dulu kartu yang lama
        try {
            $lock->block(15);
            $lastKartu = KartuInventory::where('inventory_id', $invID)->orderBy('id', 'desc')->first();
            $data = $request->all();
            $data['nilai_buku'] = $lastKartu->nilai_buku + $data['amount'];
            $ki = KartuInventory::create($data);
            //masukkan data baru dengan nilai buku yang baru yaa..
        } catch (LockTimeoutException $e) {
            $lock->release();
            return ['status' => 0, 'msg' => 'lock time error'];
        } catch (Throwable $th) {
            $lock->release();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        } finally {
            $lock->release();
            return ['status' => 1, 'msg' => $ki];
        }
        return [
            'status' => 0,
            'msg' => 'something error ?'
        ];
    }
}
