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
        
    });



});




Route::get('/test-error/{code}', function ($code) {
    abort($code);
});


require __DIR__.'/auth.php';

