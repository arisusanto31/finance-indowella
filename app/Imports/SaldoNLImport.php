<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SaldoNLImport implements ToCollection
{
    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public function collection(Collection $rows)
    {
        $this->parent->data['saldo_nl'] = $rows;
    }
}
