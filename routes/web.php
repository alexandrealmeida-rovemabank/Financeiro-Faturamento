<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParametroGlobalController; // Import do Controller
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProcessamentoLogController;
use App\Http\Controllers\CredenciadoController;
use App\Http\Controllers\FaturamentoController;
use App\http\Controllers\FaturamentoExportController;
use App\Http\Controllers\FaturaGestaoController; // <<<--- ADICIONADO

use App\Models\Fatura; 
use App\Models\ParametroGlobal;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});
Route::get('/password/reset', function () {
    return view('auth/forgot-password');
});

Route::get('/dashmetabase', [DashController::class, 'showDashboard'])
    ->middleware('allow.metabase.csp')
    ->name('dashmetabase');

Route::get('/home', [DashController::class, 'index'])->middleware(['auth', 'verified'])->name('home');

// Grupo de Rotas para Clientes
Route::middleware(['auth'])->prefix('clientes')->name('clientes.')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index');
    Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show');
    Route::get('/{cliente}/unidades', [ClienteController::class, 'getUnidades'])->name('unidades');

    // ROTA PARA SALVAR OS DADOS DO FORMULÁRIO DE PARÂMETROS
    Route::post('/{cliente}/parametros', [ClienteController::class, 'updateParametros'])->name('parametros.update');
    // Adicione esta linha no seu grupo de rotas do admin
    Route::put('/{cliente}/update-codigo-dealer', [ClienteController::class, 'updateCodigoDealer'])->name('updateCodigoDealer');;
});

Route::middleware(['auth'])->prefix('credenciados')->name('credenciados.')->group(function () {
    Route::get('/', [CredenciadoController::class, 'index'])->name('index');
    Route::get('/{credenciado}', [CredenciadoController::class, 'show'])->name('show');
    Route::get('/{credenciado}/unidades', [CredenciadoController::class, 'getUnidades'])->name('unidades');

    // ROTA PARA SALVAR OS DADOS DO FORMULÁRIO DE PARÂMETROS
    Route::post('/{credenciado}/parametros', [CredenciadoController::class, 'updateParametros'])->name('parametros.update');
});

// Grupo de Rotas para Administração
Route::middleware(['auth', 'permission:view users'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class);
    Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions'])->name('roles.assignPermissions');
    Route::resource('permissions', PermissionController::class);
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/roles', [AdminUserController::class, 'assignRoles'])->name('users.assignRoles');
    Route::post('users/{user}/permissions', [AdminUserController::class, 'assignDirectPermissions'])->name('users.assignDirectPermissions');
});

Route::middleware(['auth'])->prefix('faturamento')->name('faturamento.')->group(function () {
    // Rotas relacionadas ao faturamento podem ser adicionadas aqui
        Route::get('/', [FaturamentoController::class, 'index'])->name('faturamento.index');

});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // ROTAS PARA PARÂMETROS GLOBAIS
    Route::get('/parametros-globais', [ParametroGlobalController::class, 'index'])->name('parametros.globais.index');
    Route::post('/parametros-globais', [ParametroGlobalController::class, 'update'])->name('parametros.globais.update');
    Route::post('/parametros-globais/reset', [ParametroGlobalController::class, 'resetDefaults'])->name('parametros.globais.reset');

    // ROTAS PARA TAXAS/ALÍQUOTAS
    Route::post('/parametros-taxas', [ParametroGlobalController::class, 'storeTaxa'])->name('parametros.taxas.store');
    Route::delete('/parametros-taxas/{taxa}', [ParametroGlobalController::class, 'destroyTaxa'])->name('parametros.taxas.destroy');

    Route::post('/parametros-globais/update-banco', [App\Http\Controllers\ParametroGlobalController::class, 'updateBanco'])->name('parametros.globais.updateBanco');

    // Rota para o Log de Atividades
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
    // ROTAS PARA LOG DE PROCESSAMENTO
    
    
    Route::get('/reprocessamentos', [ProcessamentoLogController::class, 'index'])->name('processamento.logs.index');
    // NOVA ROTA PARA ACIONAR O PROCESSAMENTO MANUAL

    // CORREÇÃO: Nome do método ajustado para acionarProcessamentoManual
    Route::post('/processamento-logs/acionar', [ProcessamentoLogController::class, 'acionarProcessamentoManual'])->name('processamento.acionar');


});





Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    
    // ... (Suas outras rotas: users, roles, clientes, etc.) ...

    Route::prefix('faturamento')->name('faturamento.')->middleware(['permission:view faturamento'])->group(function () {
    
        // Etapa 1: Tela Inicial (Resumo)
        Route::get('/', [FaturamentoController::class, 'index'])->name('index');
        
        // Etapa 2: Painel de Faturamento (Abas)
        Route::get('/show', [FaturamentoController::class, 'visualizar'])->name('show');
    
        // Rota para drill-down de Subgrupos (Aba 1)
        Route::get('/get-subgrupos', [FaturamentoController::class, 'getSubgrupos'])->name('getSubgrupos');
        
        // --- Rotas AJAX (Abas 1, 2, 3) ---
        
        // Aba 1: Salvar Observações
        Route::post('/update-observacoes', [FaturamentoController::class, 'updateObservacoes'])->name('updateObservacoes')->middleware('permission:manage faturamento');
        // NOVO (Req 4): Recarregar totais da Aba 1
        Route::get('/get-resumo-geral', [FaturamentoController::class, 'getResumoAbaGeral'])->name('getResumoAbaGeral');

        // Aba 2: DataTables de Faturas Geradas
        Route::get('/get-faturas', [FaturamentoController::class, 'getFaturas'])->name('getFaturas');
        
        // Aba 2: Ação de Gerar Fatura (Modal)
        Route::post('/gerar-fatura', [FaturamentoController::class, 'gerarFatura'])->name('gerarFatura')->middleware('permission:manage faturamento');
        
        // Aba 2: Ações de CRUD (Excluir, Receber, etc.)
        Route::delete('/faturas/{fatura}', [FaturamentoController::class, 'destroyFatura'])->name('faturas.destroy');//->middleware('permission:manage faturamento');
        
        Route::post('/faturas/{fatura}/receber', [FaturamentoController::class, 'marcarRecebida'])->name('faturas.receber')->middleware('permission:manage faturamento');

        // Aba 3: DataTables de Transações
        Route::get('/get-transacoes', [FaturamentoController::class, 'getTransacoes'])->name('getTransacoes');

        // --- NOVAS ROTAS AJAX (Req 2 e 3) ---
        Route::get('/get-contratos', [FaturamentoController::class, 'getContratosCliente'])->name('getContratos');
        Route::get('/get-empenhos', [FaturamentoController::class, 'getEmpenhosPendentes'])->name('getEmpenhos');
        Route::get('/get-grupos', [FaturamentoController::class, 'getGruposPendentes'])->name('getGrupos');

        // NOVA ROTA
        Route::get('/get-valor-filtrado', [FaturamentoController::class, 'getValorFiltrado'])->name('getValorFiltrado');
        
        Route::post('/gerar-fatura', [FaturamentoController::class, 'gerarFatura'])->name('gerarFatura');

        // Rotas para o Modal de Observação
        Route::get('/faturas/{fatura}/observacao', [FaturamentoController::class, 'getObservacao'])->name('faturas.getObservacao');
        Route::put('/faturas/{fatura}/observacao', [FaturamentoController::class, 'updateObservacao'])->name('faturas.updateObservacao');

        // Rotas para Ações em Massa
        Route::post('/faturas/bulk-receber', [FaturamentoController::class, 'bulkMarcarRecebida'])->name('faturas.bulkReceber');
        Route::post('/faturas/bulk-excluir', [FaturamentoController::class, 'bulkDestroy'])->name('faturas.bulkDestroy');

        Route::get('/export/pdf', [FaturamentoExportController::class, 'exportPDF'])->name('exportPDF');
    
        Route::get('/export/xls', [FaturamentoExportController::class, 'exportXLS'])->name('exportXLS');
        
        
            
            // Rota para buscar dados da fatura para os modais
        Route::get('/fatura/{fatura}/detalhes', [FaturaGestaoController::class, 'getFaturaDetalhes'])->name('getDetalhes');

            // 1. Editar Fatura (NF, Vencimento)
        Route::put('/fatura/{fatura}', [FaturaGestaoController::class, 'update'])->name('update');

            // 2. Refaturamento (Reabrir fatura paga)
        Route::post('/fatura/{fatura}/reabrir', [FaturaGestaoController::class, 'reabrirFatura'])->name('reabrir');

            // 3. & 4. Adicionar Pagamento (Parcial/Total com comprovante)
            // Usamos POST pois 'multipart/form-data' não funciona bem com PUT/PATCH
        Route::post('/fatura/{fatura}/pagamento', [FaturaGestaoController::class, 'addPagamento'])->name('addPagamento');

        Route::get('/get-faturas-summary', [FaturamentoController::class, 'getFaturasSummary'])->name('getFaturasSummary');

        // ... (rotas existentes: getDetalhes, update, reabrir, addPagamento) ...
        
       // 1. (NOVA) Busca a lista de descontos para o modal
        Route::get('/fatura/{fatura}/descontos', [FaturaGestaoController::class, 'getDescontosLista'])
            ->name('getDescontosLista');
            
        // 2. (NOVA) Adiciona um novo desconto (fixo ou percentual)
        Route::post('/fatura/{fatura}/desconto', [FaturaGestaoController::class, 'addDesconto'])
            ->name('addDesconto')
            ->middleware('permission:manage faturamento');
            
        // 3. (NOVA) Remove um desconto específico
        Route::delete('/fatura/desconto/{desconto}', [FaturaGestaoController::class, 'removerDesconto'])
            ->name('removerDesconto')
            ->middleware('permission:manage faturamento');

            // 1. (NOVA) Busca a lista de pagamentos para o modal
        Route::get('/fatura/{fatura}/pagamentos', [FaturaGestaoController::class, 'getPagamentosLista'])
            ->name('getPagamentosLista');
            
        // 2. (NOVA) Remove um pagamento específico
        Route::delete('/fatura/pagamento/{pagamento}', [FaturaGestaoController::class, 'removerPagamento'])
            ->name('removerPagamento')
            ->middleware('permission:manage faturamento');

        // <<<--- ADICIONE ESTA NOVA ROTA PARA O PDF DA FATURA ---
        Route::get('/fatura/{fatura}/pdf', [FaturamentoExportController::class, 'exportFaturaPDF'])
            ->name('exportFaturaPDF')
            ->middleware('permission:view faturamento'); // Protege o download

        Route::get('/stats', [FaturamentoController::class, 'getIndexStats'])->name('stats');
    });


});

// Adicione esta rota no final do arquivo
Route::get('/teste-footer/{fatura}', function (Fatura $fatura) {

    // Vamos replicar os dados mínimos que a view do footer precisa
    $fatura->load('cliente'); 
    $paramGlobal = ParametroGlobal::first();

    $data = [
        'fatura' => $fatura,
        'paramGlobal' => $paramGlobal,
        // Adicione outras variáveis que o footer possa precisar
    ];

    // Isso vai tentar renderizar o blade no navegador
    // Se um 'file_get_contents' falhar, você verá o erro aqui.
    return view('admin.faturamento.exports.fatura_footer', $data);
});

// Adicione esta rota no final do arquivo
Route::get('/teste-header/{fatura}', function (Fatura $fatura) {

    // Vamos replicar os dados mínimos que a view do footer precisa
    $fatura->load('cliente'); 
    $paramGlobal = ParametroGlobal::first();

    $data = [
        'fatura' => $fatura,
        'paramGlobal' => $paramGlobal,
        // Adicione outras variáveis que o footer possa precisar
    ];

    // Isso vai tentar renderizar o blade no navegador
    // Se um 'file_get_contents' falhar, você verá o erro aqui.
    return view('admin.faturamento.exports.fatura_header', $data);
});



Route::get('/test-error/{code}', function ($code) {
    abort($code);
});


require __DIR__.'/auth.php';

