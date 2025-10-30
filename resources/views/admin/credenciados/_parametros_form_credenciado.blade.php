<form action="{{ route('credenciados.parametros.update', $credenciado->id) }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Parâmetros Específicos de IRRF</label>
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="checkbox" class="custom-control-input" id="isencao_irrf" name="isencao_irrf"
                           {{ old('isencao_irrf', $credenciado->parametroCredenciado->isencao_irrf ?? false) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="isencao_irrf">Isenção de IRRF</label>
                </div>
                <small class="form-text text-muted">Marque esta opção se este credenciado possui isenção de IRRF (Imposto de Renda Retido na Fonte).</small>
            </div>
        </div>
    </div>

    <div class="mt-3">
        @can('edit credenciado')
            <button type="submit" class="btn btn-primary">Salvar Parâmetros</button>
        @endcan
    </div>
</form>
