<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CredenciadoController;
use App\Http\Controllers\ParametroGlobalController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProcessamentoLogController;
use App\Http\Controllers\FaturamentoController;
use App\Http\Controllers\FaturamentoExportController;
use App\Http\Controllers\FaturaGestaoController;

use App\Models\Fatura;
use App\Models\ParametroGlobal;

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('auth/login'));
Route::get('/password/reset', fn () => view('auth/forgot-password'));

Route::get('/dashmetabase', [DashController::class, 'showDashboard'])
    ->middleware('allow.metabase.csp')
    ->name('dashmetabase');

Route::get('/home', [DashController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| CLIENTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('clientes')->name('clientes.')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index');
    Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show');
    Route::get('/{cliente}/unidades', [ClienteController::class, 'getUnidades'])->name('unidades');
    Route::post('/{cliente}/parametros', [ClienteController::class, 'updateParametros'])->name('parametros.update');
    Route::put('/{cliente}/update-codigo-dealer', [ClienteController::class, 'updateCodigoDealer'])->name('updateCodigoDealer');
});

/*
|--------------------------------------------------------------------------
| CREDENCIADOS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('credenciados')->name('credenciados.')->group(function () {
    Route::get('/', [CredenciadoController::class, 'index'])->name('index');
    Route::get('/{credenciado}', [CredenciadoController::class, 'show'])->name('show');
    Route::get('/{credenciado}/unidades', [CredenciadoController::class, 'getUnidades'])->name('unidades');
    Route::post('/{credenciado}/parametros', [CredenciadoController::class, 'updateParametros'])->name('parametros.update');
});

/*
|--------------------------------------------------------------------------
| ADMINISTRAÇÃO – USUÁRIOS, PERMISSÕES, LOGS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:view users'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions'])->name('roles.assignPermissions');

        Route::resource('permissions', PermissionController::class);
        Route::resource('users', AdminUserController::class);

        Route::post('users/{user}/roles', [AdminUserController::class, 'assignRoles'])->name('users.assignRoles');
        Route::post('users/{user}/permissions', [AdminUserController::class, 'assignDirectPermissions'])->name('users.assignDirectPermissions');
});

/*
|--------------------------------------------------------------------------
| PARÂMETROS GLOBAIS & LOGS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/parametros-globais', [ParametroGlobalController::class, 'index'])->name('parametros.globais.index');
        Route::post('/parametros-globais', [ParametroGlobalController::class, 'update'])->name('parametros.globais.update');
        Route::post('/parametros-globais/reset', [ParametroGlobalController::class, 'resetDefaults'])->name('parametros.globais.reset');

        Route::post('/parametros-taxas', [ParametroGlobalController::class, 'storeTaxa'])->name('parametros.taxas.store');
        Route::delete('/parametros-taxas/{taxa}', [ParametroGlobalController::class, 'destroyTaxa'])->name('parametros.taxas.destroy');

        Route::post('/parametros-globais/update-banco', [ParametroGlobalController::class, 'updateBanco'])->name('parametros.globais.updateBanco');

        Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
        Route::get('/reprocessamentos', [ProcessamentoLogController::class, 'index'])->name('processamento.logs.index');
        Route::post('/processamento-logs/acionar', [ProcessamentoLogController::class, 'acionarProcessamentoManual'])->name('processamento.acionar');
});

/*
|--------------------------------------------------------------------------
| FATURAMENTO – PAINEL COMPLETO
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('faturamento')
    ->name('faturamento.')
    ->group(function () {

        /*
        |---------------------- TELA PRINCIPAL -------------------------
        */
        Route::get('/', [FaturamentoController::class, 'index'])->name('index');
        Route::get('/show', [FaturamentoController::class, 'visualizar'])->name('show');

        /*
        |---------------------- ABA 1 – RESUMO -------------------------
        */
        Route::get('/get-subgrupos', [FaturamentoController::class, 'getSubgrupos'])->name('getSubgrupos');
        Route::post('/update-observacoes', [FaturamentoController::class, 'updateObservacoes'])
            ->middleware('permission:manage faturamento')
            ->name('updateObservacoes');
        Route::get('/get-resumo-geral', [FaturamentoController::class, 'getResumoAbaGeral'])->name('getResumoAbaGeral');

        /*
        |---------------------- ABA 2 – FATURAS ------------------------
        */
        Route::get('/get-faturas', [FaturamentoController::class, 'getFaturas'])->name('getFaturas');
        Route::post('/gerar-fatura', [FaturamentoController::class, 'gerarFatura'])
            ->middleware('permission:manage faturamento')
            ->name('gerarFatura');

        Route::delete('/faturas/{fatura}', [FaturamentoController::class, 'destroyFatura'])->name('faturas.destroy');
        Route::post('/faturas/{fatura}/receber', [FaturamentoController::class, 'marcarRecebida'])
            ->middleware('permission:manage faturamento')
            ->name('faturas.receber');

        

         /*
        |---------------------- OBSERVAÇÃO FATURA---------------------
        */
        Route::get('/faturas/{fatura}/observacao', [FaturamentoController::class, 'getObservacao'])->name('faturas.getObservacao');
        Route::put('/faturas/{fatura}/observacao', [FaturamentoController::class, 'updateObservacao'])->name('faturas.updateObservacao');


         /*
        |---------------------- AÇÕES EM MASSA FATURA ---------------------
        */
        Route::post('/faturas/bulk-receber', [FaturamentoController::class, 'bulkMarcarRecebida'])->name('faturas.bulkReceber');
        Route::post('/faturas/bulk-excluir', [FaturamentoController::class, 'bulkDestroy'])->name('faturas.bulkDestroy');

        /*
        |---------------------- ABA 3 – TRANSAÇÕES ---------------------
        */
        Route::get('/get-transacoes', [FaturamentoController::class, 'getTransacoes'])->name('getTransacoes');

        /*
        |---------------------- FILTROS ----------------------
        */
        Route::get('/get-contratos', [FaturamentoController::class, 'getContratosCliente'])->name('getContratos');
        Route::get('/get-empenhos', [FaturamentoController::class, 'getEmpenhosPendentes'])->name('getEmpenhos');
        Route::get('/get-grupos', [FaturamentoController::class, 'getGruposPendentes'])->name('getGrupos');
        Route::get('/get-valor-filtrado', [FaturamentoController::class, 'getValorFiltrado'])->name('getValorFiltrado');
        Route::get('/get-faturas-summary', [FaturamentoController::class, 'getFaturasSummary'])->name('getFaturasSummary');

        /*
        |---------------------- MODAIS DE FATURA ----------------------
        */
        Route::get('/fatura/{fatura}/detalhes', [FaturaGestaoController::class, 'getFaturaDetalhes'])->name('getDetalhes');
        Route::put('/fatura/{fatura}', [FaturaGestaoController::class, 'update'])->name('update');
        Route::post('/fatura/{fatura}/reabrir', [FaturaGestaoController::class, 'reabrirFatura'])->name('reabrir');
        Route::post('/fatura/{fatura}/pagamento', [FaturaGestaoController::class, 'addPagamento'])->name('addPagamento');

        /*
        |---------------------- DESCONTOS ----------------------
        */
        Route::get('/fatura/{fatura}/descontos', [FaturaGestaoController::class, 'getDescontosLista'])->name('getDescontosLista');
        Route::post('/fatura/{fatura}/desconto', [FaturaGestaoController::class, 'addDesconto'])
            ->middleware('permission:manage faturamento')
            ->name('addDesconto');
        Route::delete('/fatura/desconto/{desconto}', [FaturaGestaoController::class, 'removerDesconto'])
            ->middleware('permission:manage faturamento')
            ->name('removerDesconto');

        /*
        |---------------------- PAGAMENTOS ----------------------
        */
        Route::get('/fatura/{fatura}/pagamentos', [FaturaGestaoController::class, 'getPagamentosLista'])->name('getPagamentosLista');
        Route::delete('/fatura/pagamento/{pagamento}', [FaturaGestaoController::class, 'removerPagamento'])
            ->middleware('permission:manage faturamento')
            ->name('removerPagamento');

        /*
        |---------------------- EXPORTAÇÃO ----------------------
        */
        Route::get('/export/pdf', [FaturamentoExportController::class, 'exportPDF'])->name('exportPDF');
        Route::get('/export/xls', [FaturamentoExportController::class, 'exportXLS'])->name('exportXLS');
        Route::get('/fatura/{fatura}/pdf', [FaturamentoExportController::class, 'exportFaturaPDF'])
            ->middleware('permission:view faturamento')
            ->name('exportFaturaPDF');

        Route::get('/stats', [FaturamentoController::class, 'getIndexStats'])->name('stats');
});


/*
|--------------------------------------------------------------------------
| TESTES DE VIEW DO PDF (HEADER/FOOTER)
|--------------------------------------------------------------------------
*/

Route::get('/teste-footer/{fatura}', function (Fatura $fatura) {
    $fatura->load('cliente');
    $data = ['fatura' => $fatura, 'paramGlobal' => ParametroGlobal::first()];
    return view('admin.faturamento.exports.fatura_footer', $data);
});

Route::get('/teste-header/{fatura}', function (Fatura $fatura) {
    $fatura->load('cliente');
    $data = ['fatura' => $fatura, 'paramGlobal' => ParametroGlobal::first()];
    return view('admin.faturamento.exports.fatura_header', $data);
});

/*
|--------------------------------------------------------------------------
| TESTE DE ERRO
|--------------------------------------------------------------------------
*/

Route::get('/test-error/{code}', function ($code) {
    abort($code);
});

require __DIR__.'/auth.php';
