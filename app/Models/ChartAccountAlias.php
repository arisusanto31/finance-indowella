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
        'name',
        'is_child',
        'level',
        'reference_model',
        'account_type',
    ];

      public function parent()
    {
        return $this->belongsTo('App\Models\ChartAccount', 'parent_id');
    }
    public function childs()
    {
        return $this->hasMany('App\Models\ChartAccount', 'parent_id');
    }

    public function scopeAktif($q){

    return $q;
    }
    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class, 'chart_account_id');
    }

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

        static::addGlobalScope('aktif', function ($query) {
            $from = $query->getQuery()->from ?? 'chart_account_aliases'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }
            $query->where("{$alias}.is_deleted", false);
        });
    }

    public static function createOrUpdate(Request $request)
    {
        $coaID = $request->input('chart_account_id');
        $codeGroup = $request->input('code_group');
        $name = $request->input('name');
        $referenceModel = $request->input('reference_model');

        $alias = ChartAccountAlias::where('code_group', $codeGroup)->first();
        if ($alias) {
            $alias->update([
                'name' => $name,
                'reference_model' => $referenceModel,
            ]);
        } else {
            $alias = new ChartAccountAlias();
            $alias->book_journal_id = bookID();
            $alias->chart_account_id = $coaID;
            $alias->code_group = $codeGroup;
            $alias->name = $name;
            $alias->reference_model = $referenceModel;
            $alias->save();
        }

        return ['status' => 1, 'msg' => $alias];
    }
}
