{{-- resources/views/admin/clientes/_parametros_form.blade.php --}}
<form action="{{ route('clientes.parametros.update', $empresa->id) }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="ativar_globais_{{ $empresa->id }}"
                           name="ativar_parametros_globais"
                           {{ old('ativar_parametros_globais', $empresa->ParametroCliente->ativar_parametros_globais ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="ativar_globais_{{ $empresa->id }}">
                        Utilizar Parâmetros Globais
                    </label>
                </div>
                <small class="form-text text-muted">
                    Se ativado, as configurações abaixo serão ignoradas e o sistema usará as regras globais.
                </small>
            </div>
        </div>
    </div>

    <hr>

    {{-- Wrapper para os parâmetros específicos --}}
    <div class="parametros-especificos">
        <h5>Parâmetros Específicos</h5>

        <div class="row align-items-center">
            {{-- Switch Descontar IR --}}
            <div class="col-md-6">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="descontar_ir_{{ $empresa->id }}"
                               name="descontar_ir_fatura"
                               {{ old('descontar_ir_fatura', $empresa->ParametroCliente->descontar_ir_fatura ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="descontar_ir_{{ $empresa->id }}">
                            Descontar IR na Fatura
                        </label>
                    </div>
                </div>
            </div>

            {{-- Campo Dias para o Vencimento --}}
            <div class="col-md-6">
                @php
                    // Define o valor padrão conforme tipo de empresa
                    $isPublico = $empresa->empresa_tipo_id == 2;
                    $defaultDias = $isPublico ? 30 : 15;
                @endphp
                <div class="form-group">
                    <label for="dias_vencimento_{{ $empresa->id }}">Dias para o Vencimento</label>
                    <input type="number"
                           class="form-control"
                           id="dias_vencimento_{{ $empresa->id }}"
                           name="dias_vencimento"
                           value="{{ old('dias_vencimento', $empresa->ParametroCliente->dias_vencimento ?? $defaultDias) }}"
                           min="0"
                           placeholder="Ex: {{ $defaultDias }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                            class="custom-control-input"
                            id="isento_ir_{{ $empresa->id }}"
                            name="isento_ir"
                            {{ old('isento_ir', $empresa->ParametroCliente->isento_ir ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="isento_ir_{{ $empresa->id }}">
                            Isento de IR
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="mt-3">
        @can('edit cliente')
            <button type="submit" class="btn btn-primary">Salvar Parâmetros</button>
        @else
            <!-- <button type="submit" class="btn btn-primary" disabled>
                Salvar Parâmetros (Sem permissão)
            </button> -->
        @endcan
    </div>
</form>

@push('js')
<script>
    // Evita duplicação
    if (typeof window.parametrosScriptLoaded === 'undefined') {
        window.parametrosScriptLoaded = true;

        $(document).ready(function() {

            // Travar/destravar parâmetros específicos
            function toggleParametrosEspecificos() {
                $('input[name="ativar_parametros_globais"]').each(function() {
                    const isChecked = $(this).is(':checked');
                    const form = $(this).closest('form');
                    const wrapper = form.find('.parametros-especificos');

                    // Desabilita todos os inputs e aplica opacidade
                    wrapper.find('input, select').prop('disabled', isChecked);
                    wrapper.css('opacity', isChecked ? 0.5 : 1);
                });
            }

            // Executa ao carregar
            toggleParametrosEspecificos();

            // Reage a mudanças
            $(document).on('change', 'input[name="ativar_parametros_globais"]', function() {
                toggleParametrosEspecificos();
            });
        });
    }
</script>
@endpush
