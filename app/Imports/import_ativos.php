<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\estoque;
use App\Models\lote;

class import_ativos implements ToCollection, WithHeadingRow
{
    private $lote_id;

    public function __construct($lote_id)
    {
        $this->lote_id = $lote_id;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows->slice(1) as $row) {
            estoque::create([
                'categoria' => $row[0],
                'fabricante' => $row[1],
                'modelo' => $row[2],
                'numero_serie' => $row[3],
                'status' => $row[4],
                'observacao' => "",
                'historico' => "",
                'id_lote' => $this->lote_id,
            ]);
        }
    }
}
