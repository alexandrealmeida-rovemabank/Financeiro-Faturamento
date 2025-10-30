<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CredenciadoController;
use App\Http\Controllers\AbastecimentoImpressaoController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\LogisticaController;
use App\Http\Controllers\DashController;
use App\Mail\mytestemail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Models\parametros_correios_cartao;
use App\Http\Controllers\ProcessosController;
use App\Http\Controllers\LogisticaJumaController;
use App\Http\Controllers\InvetarioCartoesController;
use App\Http\Controllers\InvetarioEstoqueController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;


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

//Route::get('/dashmetabase', [DashController::class, 'showDashboard']);
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//MODULO ABASTECIMENTO
// Visualizar sistema
Route::group(['middleware' => ['permission:visualizar abastecimento']], function () {
    Route::get('/abastecimento/impressao/index', [AbastecimentoImpressaoController::class, 'index'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.index');
    Route::get('/abastecimento/impressao/edit/{id}', [AbastecimentoImpressaoController::class, 'Editar_lote'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit');

});

// Criar sistema
Route::group(['middleware' => ['permission:criar abastecimento']], function () {
    Route::get('/abastecimento/impressao/create', [AbastecimentoImpressaoController::class, 'importar'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.importar');

});

// Importar sistema
Route::group(['middleware' => ['permission:criar abastecimento']], function () {
    Route::post('/abastecimento/impressao/processamento', [AbastecimentoImpressaoController::class, 'processamento'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.processamento');

});

// Editar sistema
Route::group(['middleware' => ['permission:editar abastecimento']], function () {
    Route::get('/abastecimento/impressao/edit/cartao/{id}', [AbastecimentoImpressaoController::class, 'edit_cartao'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit.cartao');
    Route::get('/abastecimento/impressao/edit/status/{id}', [AbastecimentoImpressaoController::class, 'edit_status'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit.status');

});
// Excluir sistema
Route::group(['middleware' => ['permission:excluir abastecimento']], function () {
Route::get('/abastecimento/impressao/lote/exluir/{id}', [AbastecimentoImpressaoController::class, 'excluir_lote'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.lote.excluir');
});

//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//MODULO SISTEMA
// Visualizar sistema
Route::group(['middleware' => ['permission:visualizar sistema']], function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::get('/processos', [ProcessosController::class, 'index'])->name('processos.index');
    Route::get('/progresso', [ProcessosController::class, 'progresso'])->name('progresso');

});

// Criar sistema
Route::group(['middleware' => ['permission:criar sistema']], function () {
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
});

// Editar sistema
Route::group(['middleware' => ['permission:editar sistema']], function () {
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    
});
// Excluir sistema
Route::group(['middleware' => ['permission:excluir sistema']], function () {
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::group(['middleware' => ['auth', 'role:Admin']], function () {
    Route::resource('roles', RoleController::class)->except(['show']);
});


//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//Dashbord
Route::get('/home', [DashController::class, 'index'])->middleware(['auth', 'verified'])->name('home');

//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//MODULO CREDENCIADO
// Visualizar
Route::group(['middleware' => ['permission:visualizar credenciado']], function () {
    Route::get('/credenciado/index', [CredenciadoController::class, 'index'])->name('credenciado.index');
    Route::get('credenciado/{id}/view', [CredenciadoController::class, 'view'])->name('credenciado.view');
});

// Criar credenciados
Route::group(['middleware' => ['permission:criar credenciado']], function () {
    Route::get('/credenciado/create', function () {
        return view('credenciado.create');
    })->name('credenciado.create');
    Route::post('/credenciado/store', [CredenciadoController::class, 'store'])->middleware(['auth', 'verified'])->name('credenciado.store');
});

// Editar credenciados
Route::group(['middleware' => ['permission:editar credenciado']], function () {
    Route::get('credenciado/{id}/edit', [CredenciadoController::class, 'edit'])->name('credenciado.edit');
    Route::put('credenciado/{id}', [CredenciadoController::class, 'update'])->name('credenciado.update');
    Route::put('/credenciado/atualizar/{id}', [CredenciadoController::class, 'update'])->middleware(['auth', 'verified'])->name('credenciado.atualizar');
});

// Gerar PDF
Route::group(['middleware' => ['permission:gerar credenciado']], function () {
    Route::get('credenciado/{id}/gerar-pdf', [CredenciadoController::class, 'gerarPDF'])->name('credenciado.gerarPDF');
});

//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//MODULO LOTE E ESTOQUE
// Visualizar
Route::group(['middleware' => ['permission:visualizar estoque']], function () {
    //lote
    Route::get('/estoque/lote/index', [LoteController::class, 'index'])->middleware(['auth', 'verified'])->name('estoque.lote.index');
    //estoque
    Route::get('/estoque/index', [EstoqueController::class, 'index'])->middleware(['auth', 'verified'])->name('estoque.index');
    Route::get('/estoque/index/historico', [EstoqueController::class, 'getHistorico'])->middleware(['auth', 'verified'])->name('estoque.historico');
    Route::get('/estoque/index/historico/credenciado', [EstoqueController::class, 'getHistoricocredenciado'])->middleware(['auth', 'verified'])->name('estoque.historico.credenciado');
    Route::get('/estoque/historico/{id}', [EstoqueController::class, 'historico'])->middleware(['auth', 'verified'])->name('estoque.historico');

});

// Criar 
Route::group(['middleware' => ['permission:criar estoque']], function () {
    //lote
    Route::post('/estoque/lote/create', [LoteController::class, 'create'])->middleware(['auth', 'verified'])->name('estoque.lote.create');
    //estoque
    Route::post('/estoque/create', [EstoqueController::class, 'create'])->middleware(['auth', 'verified'])->name('estoque.create');
});

// Editar 
Route::group(['middleware' => ['permission:editar estoque']], function () {
    //lote
    Route::get('/estoque/lote/edit/{id}', [LoteController::class, 'edit'])->middleware(['auth', 'verified'])->name('estoque.lote.edit');
    //estoque
    Route::get('/estoque/edit/{id}', [EstoqueController::class, 'edit'])->middleware(['auth', 'verified'])->name('estoque.edit');
    Route::get('/terminal/vincular/{crendeciado_id}', [TerminalController::class, 'vincular'])->middleware(['auth', 'verified'])->name('terminal.vincular');
    Route::get('/terminal/desvincular/{id}', [TerminalController::class, 'desvincular'])->middleware(['auth', 'verified'])->name('terminal.desvincular');
});

// EXCLUIR
Route::group(['middleware' => ['permission:excluir estoque']], function () {
    //lote
    Route::put('/estoque/lote/excluir/{id}', [LoteController::class, 'excluir'])->middleware(['auth', 'verified'])->name('estoque.lote.excluir');
    //estoque
    Route::get('/estoque/excluir/{id}', [EstoqueController::class, 'excluir'])->middleware(['auth', 'verified'])->name('estoque.excluir');
});

// importar
Route::group(['middleware' => ['permission:excluir estoque']], function () {
    Route::get('/estoque/import', [EstoqueController::class, 'import'])->middleware(['auth', 'verified'])->name('estoque.import');
    Route::post('/estoque/processamento', [EstoqueController::class, 'processamento'])->middleware(['auth', 'verified'])->name('estoque.processamento');
});

//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//MODULO LOGISTICA
// Visualizar
Route::group(['middleware' => ['permission:visualizar logistica']], function () {
    Route::get('/logistica/correios/index', [LogisticaController::class, 'index_correios'])->middleware(['auth', 'verified'])->name('logistica.correios.index');
    Route::get('/logistica/correios/visualizar/{id}', [LogisticaController::class, 'view'])->middleware(['auth', 'verified'])->name('logistica.correios.visualizar');
    Route::get('/logistica/correios/rastreio',  [LogisticaController::class, 'rastreio_index'] )->middleware(['auth', 'verified'])->name('logistica.correios.rastreio');
    Route::post('/logistica/correios/rastrear', [LogisticaController::class,'rastrear'])->middleware(['auth', 'verified'])->name('logistica.correios.rastrear');
    Route::get('/rastrear_index/{etiqueta}', [LogisticaController::class, 'rastrear_index']);
    Route::get('/logistica/correios/consultarPedido', [LogisticaController::class, 'acompanharPedido'])->middleware(['auth', 'verified'])->name('logistica.correios.consultarPedido');
    Route::get('/verificarColeta/{cep}/{cod_servico}', [LogisticaController::class, 'Verificar_Coleta'])->middleware(['auth', 'verified'])->name('logistica.correios.coleta');
});

// Criar 
Route::group(['middleware' => ['permission:criar logistica']], function () {
    Route::get('/logistica/correios/create',  function () { $parametros = parametros_correios_cartao::all();  return view('logistica.correios.create', compact('parametros'));} )->middleware(['auth', 'verified'])->name('logistica.correios.create');
    Route::post('/logistica/correios/solicitarPostagemReversa', [LogisticaController::class, 'solicitarPostagemReversa'])->middleware(['auth', 'verified'])->name('logistica.correios.solicitarPostagemReversa');
    Route::get('/logistica/correios/buscarCartao/{contratoSelecionado}',  [LogisticaController::class, 'buscarNumerosCartao'])->middleware(['auth', 'verified'])->name('logistica.correios.buscar-numeros-cartao');

});

// EXCLUIR 
Route::group(['middleware' => ['permission:excluir logistica']], function () {
    Route::get('/logistica/correios/cancelar/{id}', [LogisticaController::class, 'cancelarPedido'])->middleware(['auth', 'verified'])->name('logistica.correios.cancelar');


});


//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//MODULO LOGISTICA

//Lojistica->Juma
Route::get('/logistica/juma/index', [LogisticaJumaController::class, 'index_juma'])->middleware(['auth', 'verified'])->name('logistica.juma.index');

// Route::get('/estoque/index/historico', [EstoqueController::class, 'getHistorico'])->middleware(['auth', 'verified'])->name('estoque.historico');
// Route::get('/estoque/index/historico/credenciado', [EstoqueController::class, 'getHistoricocredenciado'])->middleware(['auth', 'verified'])->name('estoque.historico.credenciado');
// Route::post('/estoque/create', [EstoqueController::class, 'create'])->middleware(['auth', 'verified'])->name('estoque.create');
// Route::get('/estoque/import', [EstoqueController::class, 'import'])->middleware(['auth', 'verified'])->name('estoque.import');
// Route::post('/estoque/processamento', [EstoqueController::class, 'processamento'])->middleware(['auth', 'verified'])->name('estoque.processamento');
// Route::get('/estoque/edit/{id}', [EstoqueController::class, 'edit'])->middleware(['auth', 'verified'])->name('estoque.edit');
// Route::get('/estoque/excluir/{id}', [EstoqueController::class, 'excluir'])->middleware(['auth', 'verified'])->name('estoque.excluir');
// Route::get('/estoque/historico/{id}', [EstoqueController::class, 'historico'])->middleware(['auth', 'verified'])->name('estoque.historico');

// routes/web.php
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//MODULO RELATORIOS

// Route::get('/inventario', [InvetarioCartoesController::class, 'index'])->name('inventario.index');
// Route::get('/inventario/relatorio/{tipo}', [InvetarioCartoesController::class, 'visualizarRelatorio'])->name('inventario.visualizarRelatorio');
// Route::get('/inventario/dados/{tipo}', [InvetarioCartoesController::class, 'dados'])->name('inventario.dados');
// Route::get('/inventario/gerar/{tipo}/{acao}', [InvetarioCartoesController::class, 'gerar'])->name('inventario.gerar'); // Para PDF
// Route::post('/inventario/exportar-pdf/{tipo}', [InvetarioCartoesController::class, 'exportarPdf'])->name('inventario.exportarPdf'); // Para PDF com filtros

Route::group(['middleware' => ['permission:visualizar relatorio']], function () {
Route::prefix('inventario/cartao')->name('inventario.cartao.')->group(function () {
    Route::get('/index', [InvetarioCartoesController::class, 'index'])->name('index');
    Route::get('/relatorio/{tipo}', [InvetarioCartoesController::class, 'visualizarRelatorio'])->name('visualizarRelatorio');
    Route::get('/dados/{tipo}', [InvetarioCartoesController::class, 'dados'])->name('dados');
    Route::get('/gerar/{tipo}/{acao}', [InvetarioCartoesController::class, 'gerar'])->name('gerar'); // Para PDF (visualizar/baixar)
    Route::get('/exportar-pdf/{tipo}', [InvetarioCartoesController::class, 'exportarPdf'])->name('exportarPdf'); // Para PDF com filtros
});
});


// Visualizar
Route::group(['middleware' => ['permission:visualizar relatorio']], function () {
    // Route::get('/inventario/cartao/index', [InvetarioCartoesController::class, 'index'])->middleware(['auth', 'verified'])->name('inventario.cartao.index');
    Route::get('/inventario/estoque/index', [InvetarioEstoqueController::class, 'index'])->middleware(['auth', 'verified'])->name('inventario.estoque.index');
    Route::get('/inventario/relatorio/{tipo}/visualizar', [InvetarioEstoqueController::class, 'visualizarRelatorio'])->name('inventario.visualizar');
    Route::get('/inventario/relatorio/{tipo}/dados', [InvetarioEstoqueController::class, 'dados'])->name('inventario.dados');

});

// GERAR
Route::group(['middleware' => ['permission:gerar logistica']], function () {
    Route::get('/relatorios/estoque/{tipo}/pdf', [InvetarioEstoqueController::class, 'baixarPDF'])->middleware(['auth', 'verified'])->name('relatorios.estoque.pdf');
    Route::get('/relatorio/{tipo}/{acao}', [InvetarioEstoqueController::class, 'gerar']);
    Route::get('inventario/pdf/{tipo}', [InvetarioEstoqueController::class, 'exportarPdf'])->name('inventario.exportarPdf');

});

//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

//consulta API
Route::get('/buscar-cnpj/{cnpj}', [CredenciadoController::class, 'buscarcnpj'])->middleware(['auth', 'verified'])->name('credenciado.cnpj');



Route::get('/testroute', function() {
    $name = "Funny Coder";

    Mail::to('alexandre.almeida@uzzipay.com')->send(new mytestemail($name));
});

//testes Debug
Route::get('post/root_rastrear_index/{etiqueta}', [LogisticaController::class, 'rastrear_index']);
Route::get('get/root_gerarToken_correios', [LogisticaController::class, 'gerarToken']);
Route::get('post/root_pedido', [LogisticaController::class, 'acompanharpedido3']);//tem que descomentar a função na controller

// Rotas credenciados
// Route::get('/credenciado/index', [CredenciadoController::class, 'index'])->middleware(['auth', 'verified'])->name('credenciado.index');
// Route::get('/credenciado/create', function () {return view('credenciado.create');})->middleware(['auth', 'verified'])->name('credenciado.create');
// Route::post('/credenciado/store', [CredenciadoController::class, 'store'])->middleware(['auth', 'verified'])->name('credenciado.store');
// Route::get('/credenciado/editar/{id}', [CredenciadoController::class, 'edit'])->middleware(['auth', 'verified'])->name('credenciado.edit');
//Route::get('/credenciado/visualizar/{id}', [CredenciadoController::class, 'view'])->middleware(['auth', 'verified'])->name('credenciado.visualizar');
// Route::get('/credenciado/pdf/{id}', [CredenciadoController::class, 'gerarpdf'])->middleware(['auth', 'verified'])->name('credenciado.pdf');
// // Route::get('/credenciado/pdf2/{id}', [CredenciadoController::class, 'gerarpdf2'])->middleware(['auth', 'verified'])->name('credenciado.pdf');
//Route::put('/credenciado/atualizar/{id}', [CredenciadoController::class, 'update'])->middleware(['auth', 'verified'])->name('credenciado.atualizar');
//Route::get('/buscar-cnpj/{cnpj}', [CredenciadoController::class, 'buscarcnpj'])->middleware(['auth', 'verified'])->name('credenciado.cnpj');

// Rotas User
// Route::group(['middleware' => ['auth', 'role:Admin']], function () {
//     Route::get('users', [UserController::class, 'index'])->name('users.index');
//     Route::get('users/data', [UserController::class, 'data'])->name('users.data');
//     Route::get('users/create', [UserController::class, 'create'])->name('users.create');
//     Route::post('users', [UserController::class, 'store'])->name('users.store');
//     Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
//     Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
//     Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
// });

// Rotas Abastecimento
// Route::get('/abastecimento/impressao/index', [AbastecimentoImpressaoController::class, 'index'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.index');
// Route::get('/abastecimento/impressao/create', [AbastecimentoImpressaoController::class, 'importar'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.importar');
// Route::post('/abastecimento/impressao/processamento', [AbastecimentoImpressaoController::class, 'processamento'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.processamento');
// Route::get('/abastecimento/impressao/edit/{id}', [AbastecimentoImpressaoController::class, 'Editar_lote'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit');
// Route::get('/abastecimento/impressao/edit/cartao/{id}', [AbastecimentoImpressaoController::class, 'edit_cartao'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit.cartao');
// Route::get('/abastecimento/impressao/edit/status/{id}', [AbastecimentoImpressaoController::class, 'edit_status'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit.status');
// Route::get('/abastecimento/impressao/lote/exluir/{id}', [AbastecimentoImpressaoController::class, 'excluir_lote'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.lote.excluir');


// Rotas impressão de cartões

// // Rotas Lote
// Route::get('/estoque/lote/index', [LoteController::class, 'index'])->middleware(['auth', 'verified'])->name('estoque.lote.index');
// //Route::get('/estoque/lote/create', function () {return view('estoque.lote.create');})->middleware(['auth', 'verified'])->name('estoque.lote.create');
// Route::post('/estoque/lote/create', [LoteController::class, 'create'])->middleware(['auth', 'verified'])->name('estoque.lote.create');
// Route::get('/estoque/lote/edit/{id}', [LoteController::class, 'edit'])->middleware(['auth', 'verified'])->name('estoque.lote.edit');
// Route::put('/estoque/lote/excluir/{id}', [LoteController::class, 'excluir'])->middleware(['auth', 'verified'])->name('estoque.lote.excluir');

// //Terminal
// Route::get('/terminal/vincular/{crendeciado_id}', [TerminalController::class, 'vincular'])->middleware(['auth', 'verified'])->name('terminal.vincular');
// Route::get('/terminal/desvincular/{id}', [TerminalController::class, 'desvincular'])->middleware(['auth', 'verified'])->name('terminal.desvincular');
// // Route::get('/estoque/import', [EstoqueController::class, 'import'])->middleware(['auth', 'verified'])->name('estoque.import');
// // Route::post('/estoque/processamento', [EstoqueController::class, 'processamento'])->middleware(['auth', 'verified'])->name('estoque.processamento');
// // Route::get('/estoque/edit/{id}', [EstoqueController::class, 'edit'])->middleware(['auth', 'verified'])->name('estoque.edit');
// // Route::get('/estoque/excluir/{id}', [EstoqueController::class, 'excluir'])->middleware(['auth', 'verified'])->name('estoque.excluir');
// // Route::get('/estoque/historico/{id}', [EstoqueController::class, 'historico'])->middleware(['auth', 'verified'])->name('estoque.historico');


//Lojistica->correiros

// Route::get('/logistica/correios/index', [LogisticaController::class, 'index_correios'])->middleware(['auth', 'verified'])->name('logistica.correios.index');
// Route::get('/logistica/correios/create',  function () { $parametros = parametros_correios_cartao::all();  return view('logistica.correios.create', compact('parametros'));} )->middleware(['auth', 'verified'])->name('logistica.correios.create');
// Route::post('/logistica/correios/solicitarPostagemReversa', [LogisticaController::class, 'solicitarPostagemReversa'])->middleware(['auth', 'verified'])->name('logistica.correios.solicitarPostagemReversa');
// Route::get('/logistica/correios/buscarCartao/{contratoSelecionado}',  [LogisticaController::class, 'buscarNumerosCartao'])->middleware(['auth', 'verified'])->name('logistica.correios.buscar-numeros-cartao');
// Route::get('/logistica/correios/cancelar/{id}', [LogisticaController::class, 'cancelarPedido'])->middleware(['auth', 'verified'])->name('logistica.correios.cancelar');
// Route::get('/logistica/correios/visualizar/{id}', [LogisticaController::class, 'view'])->middleware(['auth', 'verified'])->name('logistica.correios.visualizar');
// Route::get('/logistica/correios/rastreio',  [LogisticaController::class, 'rastreio_index'] )->middleware(['auth', 'verified'])->name('logistica.correios.rastreio');
// Route::post('/logistica/correios/rastrear', [LogisticaController::class,'rastrear'])->middleware(['auth', 'verified'])->name('logistica.correios.rastrear');
// Route::get('/logistica/correios/consultarPedido', [LogisticaController::class, 'acompanharPedido'])->middleware(['auth', 'verified'])->name('logistica.correios.consultarPedido');
// Route::get('/verificarColeta/{cep}/{cod_servico}', [LogisticaController::class, 'Verificar_Coleta'])->middleware(['auth', 'verified'])->name('logistica.correios.coleta');
// Route::get('/rastrear_index/{etiqueta}', [LogisticaController::class, 'rastrear_index']);


require __DIR__.'/auth.php';
