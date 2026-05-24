<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackgroundProcess extends Model
{
    //
    protected $table= 'background_process';
    protected $fillable = [
        'monitoring_url',
        'progress',
        'description_process',
        'total_task',
        'success_task',
        'failed_task',
        'status',
    ];

}
