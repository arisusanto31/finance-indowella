<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class JournalJobFailed extends Model
{
    use HasFactory;

    protected $table = 'journal_job_faileds';
    public $timestamps = true;


    public static function create(Request $request)
    {
        $journalFailed = new JournalJobFailed;
        $journalFailed->url_try_again = $request->input('url_try_again');
        $journalFailed->type = $request->input('type');
        $journalFailed->request = $request->input('request');
        $journalFailed->response = $request->input('response');
        $journalFailed->save();
        return [
            'status' => 1,
            'msg' => $journalFailed
        ];
    }

}
