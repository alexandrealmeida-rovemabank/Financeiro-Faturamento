@extends('adminlte::page')

@section('title', 'Parâmetros Globais')

@section('content_header')
    <h1 class="fw-bold text-primary">
        <i class="fas fa-shield-alt me-2"></i>Configurações de Parametros Globais
    </h1>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.session-messages')

    <div class="row">

        {{-- CARD: Configurações Gerais de Faturamento --}}
        <div class="col-md-6 mb-4">
            <div class="card card-filter mb-4 shadow-lg border-0">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Configurações Gerais de Faturamento</h5>
                </div>

                <form action="{{ route('admin.parametros.globais.update') }}" method="POST" class="h-100 d-flex flex-column">
                    @csrf

                    <div class="card-body flex-grow-1">
                        {{-- Descontar IR na Fatura --}}
                        <div class="mb-3">
                            <label for="descontar_ir_fatura" class="form-label">Descontar IR (Padrão)</label>
                            <div class="form-switch">
                                <input type="checkbox" class="form-check-input" id="descontar_ir_fatura" name="descontar_ir_fatura"
                                    {{ old('descontar_ir_fatura', $parametros->descontar_ir_fatura) ? 'checked' : '' }}>
                                <label for="descontar_ir_fatura" class="ms-2">Ativar desconto de IR padrão</label>
                            </div>
                            <small class="text-muted">
                                IR descontado na fatura dos clientes que usam parâmetros globais. Se desativado, o IR não será descontado somente sera informado na fatura.
                            </small>
                        </div>

                        {{-- Dias de Vencimento Público --}}
                        <div class="mb-3">
                            <label for="dias_vencimento_publico" class="form-label">Prazo Público (dias)</label>
                            <input type="number"
                                   class="form-control @error('dias_vencimento_publico') is-invalid @enderror"
                                   id="dias_vencimento_publico"
                                   name="dias_vencimento_publico"
                                   value="{{ old('dias_vencimento_publico', $parametros->dias_vencimento_publico) }}"
                                   required min="0">
                            @error('dias_vencimento_publico')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Vencimento de faturas de clientes Federais, Estaduais e Municipais.
                            </small>
                        </div>

                        {{-- Dias de Vencimento Privado --}}
                        <div>
                            <label for="dias_vencimento_privado" class="form-label">Prazo Privado (dias)</label>
                            <input type="number"
                                   class="form-control @error('dias_vencimento_privado') is-invalid @enderror"
                                   id="dias_vencimento_privado"
                                   name="dias_vencimento_privado"
                                   value="{{ old('dias_vencimento_privado', $parametros->dias_vencimento_privado) }}"
                                   required min="0">
                            @error('dias_vencimento_privado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Vencimento de faturas de clientes privados.
                            </small>
                        </div>
                    </div>
                
                    @can('edit parametros globais')
                    <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Salvar Configurações
                        </button>
                    @endcan
                    
                </form>
                        @can('reset parametros globais')
                            <form action="{{ route('admin.parametros.globais.reset') }}" method="POST"
                                  onsubmit="return confirm('Tem certeza que deseja resetar os parâmetros para os valores padrão?');">
                                @csrf
                                <button type="submit" class="btn btn-warning text-white">
                                    <i class="fas fa-undo me-1"></i>Resetar Padrões
                                </button>
                            </form>
                        @endcan
                    </div>
                
            </div>
        </div>

        {{-- CARD: Taxas/Alíquotas por Categoria --}}
        <div class="col-md-6 mb-4">
            <div class="card card-filter mb-4 shadow-lg border-0">
                <div class="card-header bg-secondary text-white d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Taxas/Alíquotas por Categoria de Produto</h5>
                </div>

                <form action="{{ route('admin.parametros.taxas.store') }}" method="POST" id="form-taxas">
                    @csrf
                    <input type="hidden" name="taxa_id" id="taxa_id">

                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="organizacao_id" class="form-label">Organização (Cliente)</label>
                                <select name="organizacao_id" id="organizacao_id"
                                        class="form-control select2 @error('organizacao_id') is-invalid @enderror" required>
                                    <option value="">Selecione...</option>
                                    @foreach($organizacoes as $org)
                                        <option value="{{ $org->id }}" {{ old('organizacao_id') == $org->id ? 'selected' : '' }}>
                                            {{ $org->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('organizacao_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="produto_categoria_id" class="form-label">Categoria de Produto</label>
                                <select name="produto_categoria_id" id="produto_categoria_id"
                                        class="form-control select2 @error('produto_categoria_id') is-invalid @enderror" required>
                                    <option value="">Selecione...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" {{ old('produto_categoria_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('produto_categoria_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="taxa_aliquota" class="form-label">Taxa/Alíquota (%)</label>
                            <input type="number" step="0.0001"
                                   class="form-control @error('taxa_aliquota') is-invalid @enderror"
                                   id="taxa_aliquota"
                                   name="taxa_aliquota"
                                   placeholder="Ex: 1.5000 para 1,5%"
                                   value="{{ old('taxa_aliquota') }}" required>
                            @error('taxa_aliquota')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Use ponto como separador decimal. Ex: 1.2000 para 1.2%.
                            </small>
                        </div>
                    </div>

                    <div class="card-footer bg-light border-top d-flex justify-content-between">
                        @can('create parametros globais')
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Salvar
                            </button>
                            <button type="button" id="btn-cancel-edit" class="btn btn-secondary" style="display:none;">
                                <i class="fas fa-times me-1"></i>Cancelar Edição
                            </button>
                        @endcan
                    </div>
                </form>

                {{-- Tabela de Taxas --}}
                <div class="table-responsive mt-3">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Organização</th>
                                <th>Categoria</th>
                                <th>Taxa (%)</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($taxas as $taxa)
                                <tr>
                                    <td>{{ $taxa->organizacao->nome ?? 'N/A' }}</td>
                                    <td>{{ $taxa->produtoCategoria->nome ?? 'N/A' }}</td>
                                    <td>{{ number_format($taxa->taxa_aliquota * 100, 2, ',', '.') }}%</td>
                                    <td class="text-center">
                                        @can('edit parametros globais')
                                            <button class="btn btn-sm btn-warning btn-edit-taxa me-1"
                                                    data-id="{{ $taxa->id }}"
                                                    data-org="{{ $taxa->organizacao_id }}"
                                                    data-cat="{{ $taxa->produto_categoria_id }}"
                                                    data-taxa="{{ $taxa->taxa_aliquota * 100 }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endcan
                                        @can('delete parametros globais')
                                            <form action="{{ route('admin.parametros.taxas.destroy', $taxa->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir esta taxa?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Nenhuma taxa/alíquota cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.5.2/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.4rem + 2px) !important;
        padding: 0.4rem 0.75rem;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "Selecione...",
            allowClear: true,
            width: '100%'
        });

        $('.btn-edit-taxa').click(function() {
            const id = $(this).data('id');
            $('#taxa_id').val(id);
            $('#organizacao_id').val($(this).data('org')).trigger('change');
            $('#produto_categoria_id').val($(this).data('cat')).trigger('change');
            $('#taxa_aliquota').val($(this).data('taxa'));

            $('#btn-cancel-edit').show();
            $('#form-taxas').find('button[type="submit"]').html('<i class="fas fa-sync-alt me-1"></i>Atualizar');
            $('html, body').animate({ scrollTop: $("#form-taxas").offset().top - 80 }, 400);
        });

        $('#btn-cancel-edit').click(function() {
            $('#form-taxas')[0].reset();
            $('#organizacao_id, #produto_categoria_id').val('').trigger('change');
            $('#taxa_id').val('');
            $(this).hide();
            $('#form-taxas').find('button[type="submit"]').html('<i class="fas fa-save me-1"></i>Salvar');
        });
    });
</script>
@endpush
