<?php

namespace App\Traits;

trait HasModelChilds
{
    public function getChilds($column)
    {
        return $this->reference_model::where($column, $this->id)->get();
    }
}
