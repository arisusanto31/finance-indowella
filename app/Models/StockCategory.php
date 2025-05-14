<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    //
    protected $table = 'stock_categories';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'parent_id',
    ];



    public static function addCategoryIfNotExists($name, $parentName = null)
    {
        $category = self::where('name', $name)->first();
        if (!$category) {

            $category = new self();
            if ($parentName) {
                $parentCategory = self::addCategoryIfNotExists($parentName, null);
                $category->parent_id = $parentCategory->id;
            }
            $category->name = $name;
            $category->save();
        }
        return $category;
    }
}
