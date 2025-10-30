<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\lote_impressao;
use App\Models\Impressao;

class YourImport implements ToCollection, WithHeadingRow
{
    private $lote_id;

    public function __construct($lote_id)
    {
        $this->lote_id = $lote_id;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows->slice(1) as $row) {
            Impressao::create([
                'placa' => $row[0],
                'modelo' => $row[1],
                'combustivel' => $row[2],
                'trilha' => $row[3],
                'numero_cartao' => $row[4],
                'cliente' => $row[5],
                'gruposubgrupo' => $row[6],
                'id_lote_impressao' => $this->lote_id,
            ]);
        }
    }
}
