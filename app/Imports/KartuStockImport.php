<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class KartuStockImport implements ToCollection
{
    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public function collection(Collection $rows)
    {
        $this->parent->data['kartu_stock'] = $rows;
    }
}
