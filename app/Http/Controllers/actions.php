<?php

function button_estoque($row) {
    $btn = '<div class="btn-group" role="group">
                <button type="button" class="btn btn-success dropdown-toggle " data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                <li><a type="button" class="dropdown-item btn-historico"  data-bs-toggle="modal" data-bs-target="#modalhistorico"
                data-id="'.$row->id.'" data-categoria="'.$row->categoria.'" data-id_lote="'.$row->lote->lote.'" data-fabricante="'.$row->fabricante.'" data-modelo="'.$row->modelo.'"
                data-numero_serie="'.$row->numero_serie.'" data-status="'.$row->status.'" data-observacao="'.$row->observacao.'" data-data_cadastro="'.$row->created_at.'" data-metodo_cadastro="'.$row->metodo_cadastro.'" >Histórico</a></li>
                    <li><a type="button" class="dropdown-item btn-editar"  data-bs-toggle="modal" data-bs-target="#modalEditar"
                        data-id="'.$row->id.'" data-categoria="'.$row->categoria.'" data-id_lote="'.$row->lote->lote.'" data-fabricante="'.$row->fabricante.'" data-modelo="'.$row->modelo.'"
                        data-numero_serie="'.$row->numero_serie.'" data-status="'.$row->status.'" data-observacao="'.$row->observacao.'" >Editar</a></li>
                    <li><a href="'.route('estoque.excluir', $row->id).'" class="dropdown-item">Excluir</a></li>
                </ul>
            </div>';


    return $btn;

}

function button_credenciado($row){

    $btn = '<div class="btn-group" role="group">
    <button type="button" class="btn btn-success dropdown-toggle " data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu">
        <li><a href="'.route('credenciado.visualizar', $row->id).'" class="dropdown-item">Visualizar</a></li>
        <li><a href="'.route('credenciado.edit', $row->id).'" class="dropdown-item">Editar</a></li>';

if($row->status == "Ativo") {
    $btn .= '<li><a class="dropdown-item" href="'.route('credenciado.edit', $row->id).'" type="submit">Inativar</a></li>';
}

if($row->status == "Inativo") {
    $btn .= '<li><a class="dropdown-item" href="'.route('credenciado.edit', $row->id).'" type="submit">Ativar</a></li>';
}

$btn .= '</ul></div>';

return $btn;


}

function button_lote_cartoes($row){
    $btn ='<div class="btn-group" role="group">
    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu">
      <li><a href="'.route('abastecimento.impressao.edit', $row->id).'" class="dropdown-item" >Visualizar </a></li>';
      if($row->status_impressao == "Importado"){
        $btn .='<li><a class="dropdown-item" href="'.route('abastecimento.impressao.edit.status', $row->id).'" type="submit">Impresso</a></li>';
      }
      if($row->status_impressao == "Importado"){
        $btn .='<li><a href="'.route('abastecimento.impressao.lote.excluir', $row->id).'" class="dropdown-item">Excluir</a></li>';
      }

    $btn .= '</ul> </div>';
    return $btn;
}

function button_lote_cartoes_impressao_editar($row, $status){
    $btn = '';
    if($status == 'Importado'){
     $btn .= '<button type="button" class="btn btn-success btn-editar"
         data-bs-toggle="modal" data-bs-target="#modalEditar"
         data-id="'.$row->id .'" data-placa="' .$row->placa .'"
         data-modelo="' .$row->modelo. '" data-combustivel="' .$row->combustivel.'"
         data-cliente="'.$row->cliente .'" data-gruposubgrupo="' .$row->gruposubgrupo.'" data-idlote="'.$row->id_lote_impressao.'">
         Editar
     </button>';
    }

    return $btn;
}

function button_logistica_correios($row){

    $btn = '<div class="btn-group" role="group">
    <button type="button" class="btn btn-success dropdown-toggle " data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu">
        <li><a href="'.route('logistica.correios.visualizar', $row->id).'" class="dropdown-item">Visualizar</a></li>';


if($row->desc_status_objeto == "A Coletar" || $row->desc_status_objeto == "A COLETAR" ) {
    $btn .= '<li><a class="dropdown-item" href="'.route('logistica.correios.cancelar', $row->id).'" type="submit">Cancelar</a></li>';
}


$btn .= '</ul></div>';

return $btn;


}

function button_logistica_juma($row){

    $btn = '<div class="btn-group" role="group">
    <button type="button" class="btn btn-success dropdown-toggle " data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu">
        <li><a href="'.route('logistica.correios.visualizar', $row->id).'" class="dropdown-item">Visualizar</a></li>';


if($row->desc_status_objeto == "A Coletar" || $row->desc_status_objeto == "A COLETAR" ) {
    $btn .= '<li><a class="dropdown-item" href="'.route('logistica.correios.cancelar', $row->id).'" type="submit">Cancelar</a></li>';
}


$btn .= '</ul></div>';

return $btn;


}
