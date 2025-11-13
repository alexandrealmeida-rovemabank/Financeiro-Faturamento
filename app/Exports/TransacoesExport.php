<?php

namespace App\Exports;

use App\Models\TransacaoFaturamento;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class TransacoesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;
    protected $parametrosAtivos;
    protected $taxas;

    public function __construct($query, $parametrosAtivos, $taxas)
    {
        $this->query = $query;
        $this->parametrosAtivos = $parametrosAtivos;
        $this->taxas = $taxas;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        return $this->query;
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        // Cabeçalhos "completos" conforme solicitado
        return [
            'ID Transação',
            'Faturada?',
            'ID Fatura',
            'Data Transação',
            'ID Credenciado',
            'Credenciado',
            'ID Cliente',
            'Cliente',
            'ID Unidade',
            'Unidade',
            'ID Contrato',
            'Contrato',
            'ID Empenho',
            'Empenho',
            'ID Veículo',
            'Placa',
            'Grupo',
            'Subgrupo',
            'ID Produto',
            'Produto',
            'Qtd',
            'Valor Unitário',
            'Valor Total (Bruto)',
            'Alíquota IR Aplicada',
            'Valor IR Calculado',
        ];
    }

    /**
    * @param TransacaoFaturamento $row
    * @return array
    */
    public function map($row): array
    {
        $aliquota_ir = 0;
        if (!$this->parametrosAtivos['isento_ir']) {
            $categoriaId = optional($row->produto)->produto_categoria_id;
            $taxa = $this->taxas->get($categoriaId);
            $aliquota_ir = $taxa ? $taxa->taxa_aliquota : 0;
        }
        $valor_ir = $row->valor_total * $aliquota_ir;

        return [
            $row->id,
            $row->status_faturamento == 'pendente' ? 'Não' : 'Sim',
            $row->fatura_id,
            Carbon::parse($row->data_transacao)->format('d/m/Y H:i:s'),
            $row->credenciado_id,
            optional($row->credenciado)->razao_social ?? 'N/A',
            $row->cliente_id,
            optional($row->cliente)->razao_social ?? 'N/A',
            $row->unidade_id,
            optional($row->unidade)->razao_social ?? 'N/A',
            $row->contrato_id,
            optional($row->contrato)->numero ?? 'N/A',
            $row->empenho_id,
            optional($row->empenho)->numero_empenho ?? 'N/A',
            $row->veiculo_id,
            optional($row->veiculo)->placa ?? 'N/A',
            optional(optional(optional($row->veiculo)->grupo)->grupoPai)->nome ?? 'N/A',
            optional(optional($row->veiculo)->grupo)->nome ?? 'N/A',
            $row->produto_id,
            optional($row->produto)->nome ?? 'N/A',
            $row->quantidade,
            $row->valor_unitario,
            $row->valor_total,
            $aliquota_ir,
            $valor_ir,
        ];
    }
}