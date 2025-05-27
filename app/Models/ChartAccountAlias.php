<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChartAccountAlias extends Model
{
    //

    protected $table = "chart_account_aliases";
    protected $fillable = [
        'book_journal_id',
        'chart_account_id',
        'code_group',
        'name'
    ];


    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'chart_account_aliases'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }
            $query->where(function ($q) use ($alias) {
                $q->whereNull("{$alias}.book_journal_id")
                    ->orWhere("{$alias}.book_journal_id", bookID());
            });
        });
    }

    public static function createOrUpdate(Request $request)
    {
        $coaID = $request->input('chart_account_id');
        $codeGroup = $request->input('code_group');
        $name = $request->input('name');

        $alias = ChartAccountAlias::where('chart_account_id', $coaID)->where('code_group', $codeGroup)->first();
        if ($alias) {
            $alias->update([
                'name' => $name
            ]);
        } else {
            $alias = new ChartAccountAlias();
            $alias->book_journal_id = bookID();
            $alias->chart_account_id = $coaID;
            $alias->code_group = $codeGroup;
            $alias->name = $name;
            $alias->save();
        }

        return ['status'=>1,'msg'=>$alias];
    }
}
