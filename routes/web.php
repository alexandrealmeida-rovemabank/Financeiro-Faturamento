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


Route::get('/testroute', function() {
    $name = "Funny Coder";

    Mail::to('alexandre.almeida@uzzipay.com')->send(new mytestemail($name));
});


Route::get('/', function () {
    return view('auth/login');
});
Route::get('/password/reset', function () {
    return view('auth/forgot-password');
});



Route::get('/home', [DashController::class, 'index'])->middleware(['auth', 'verified'])->name('home');




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



// Rotas credenciados
Route::get('/credenciado/index', [CredenciadoController::class, 'index'])->middleware(['auth', 'verified'])->name('credenciado.index');
Route::get('/credenciado/create', function () {return view('credenciado.create');})->middleware(['auth', 'verified'])->name('credenciado.create');
Route::post('/credenciado/store', [CredenciadoController::class, 'store'])->middleware(['auth', 'verified'])->name('credenciado.store');
Route::get('/credenciado/editar/{id}', [CredenciadoController::class, 'edit'])->middleware(['auth', 'verified'])->name('credenciado.edit');
Route::get('/credenciado/visualizar/{id}', [CredenciadoController::class, 'view'])->middleware(['auth', 'verified'])->name('credenciado.visualizar');
Route::get('/credenciado/pdf/{id}', [CredenciadoController::class, 'gerarpdf'])->middleware(['auth', 'verified'])->name('credenciado.pdf');
// Route::get('/credenciado/pdf2/{id}', [CredenciadoController::class, 'gerarpdf2'])->middleware(['auth', 'verified'])->name('credenciado.pdf');
Route::put('/credenciado/atualizar/{id}', [CredenciadoController::class, 'update'])->middleware(['auth', 'verified'])->name('credenciado.atualizar');
Route::get('/buscar-cnpj/{cnpj}', [CredenciadoController::class, 'buscarcnpj'])->middleware(['auth', 'verified'])->name('credenciado.cnpj');


// Rotas impressão de cartões
Route::get('/abastecimento/impressao/index', [AbastecimentoImpressaoController::class, 'index'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.index');
Route::get('/abastecimento/impressao/create', [AbastecimentoImpressaoController::class, 'importar'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.importar');
Route::post('/abastecimento/impressao/processamento', [AbastecimentoImpressaoController::class, 'processamento'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.processamento');
Route::get('/abastecimento/impressao/edit/{id}', [AbastecimentoImpressaoController::class, 'Editar_lote'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit');
Route::get('/abastecimento/impressao/edit/cartao/{id}', [AbastecimentoImpressaoController::class, 'edit_cartao'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit.cartao');
Route::get('/abastecimento/impressao/edit/status/{id}', [AbastecimentoImpressaoController::class, 'edit_status'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit.status');
Route::get('/abastecimento/impressao/lote/exluir/{id}', [AbastecimentoImpressaoController::class, 'excluir_lote'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.lote.excluir');

// Rotas Lote
Route::get('/estoque/lote/index', [LoteController::class, 'index'])->middleware(['auth', 'verified'])->name('estoque.lote.index');
//Route::get('/estoque/lote/create', function () {return view('estoque.lote.create');})->middleware(['auth', 'verified'])->name('estoque.lote.create');
Route::post('/estoque/lote/create', [LoteController::class, 'create'])->middleware(['auth', 'verified'])->name('estoque.lote.create');
Route::get('/estoque/lote/edit/{id}', [LoteController::class, 'edit'])->middleware(['auth', 'verified'])->name('estoque.lote.edit');
Route::put('/estoque/lote/excluir/{id}', [LoteController::class, 'excluir'])->middleware(['auth', 'verified'])->name('estoque.lote.excluir');

// Rotas estoque
Route::get('/estoque/index', [EstoqueController::class, 'index'])->middleware(['auth', 'verified'])->name('estoque.index');
Route::get('/estoque/index/historico', [EstoqueController::class, 'getHistorico'])->middleware(['auth', 'verified'])->name('estoque.historico');
Route::get('/estoque/index/historico/credenciado', [EstoqueController::class, 'getHistoricocredenciado'])->middleware(['auth', 'verified'])->name('estoque.historico.credenciado');
Route::post('/estoque/create', [EstoqueController::class, 'create'])->middleware(['auth', 'verified'])->name('estoque.create');
Route::get('/estoque/import', [EstoqueController::class, 'import'])->middleware(['auth', 'verified'])->name('estoque.import');
Route::post('/estoque/processamento', [EstoqueController::class, 'processamento'])->middleware(['auth', 'verified'])->name('estoque.processamento');
Route::get('/estoque/edit/{id}', [EstoqueController::class, 'edit'])->middleware(['auth', 'verified'])->name('estoque.edit');
Route::get('/estoque/excluir/{id}', [EstoqueController::class, 'excluir'])->middleware(['auth', 'verified'])->name('estoque.excluir');
Route::get('/estoque/historico/{id}', [EstoqueController::class, 'historico'])->middleware(['auth', 'verified'])->name('estoque.historico');


//Terminal
Route::get('/terminal/vincular/{crendeciado_id}', [TerminalController::class, 'vincular'])->middleware(['auth', 'verified'])->name('terminal.vincular');
Route::get('/terminal/desvincular/{id}', [TerminalController::class, 'desvincular'])->middleware(['auth', 'verified'])->name('terminal.desvincular');
// Route::get('/estoque/import', [EstoqueController::class, 'import'])->middleware(['auth', 'verified'])->name('estoque.import');
// Route::post('/estoque/processamento', [EstoqueController::class, 'processamento'])->middleware(['auth', 'verified'])->name('estoque.processamento');
// Route::get('/estoque/edit/{id}', [EstoqueController::class, 'edit'])->middleware(['auth', 'verified'])->name('estoque.edit');
// Route::get('/estoque/excluir/{id}', [EstoqueController::class, 'excluir'])->middleware(['auth', 'verified'])->name('estoque.excluir');
// Route::get('/estoque/historico/{id}', [EstoqueController::class, 'historico'])->middleware(['auth', 'verified'])->name('estoque.historico');


//Lojistica

Route::get('/logistica/correios/index', [LogisticaController::class, 'index_correios'])->middleware(['auth', 'verified'])->name('logistica.correios.index');
Route::get('/logistica/correios/create',  function () { $parametros = parametros_correios_cartao::all();  return view('logistica.correios.create', compact('parametros'));} )->middleware(['auth', 'verified'])->name('logistica.correios.create');
Route::post('/logistica/correios/solicitarPostagemReversa', [LogisticaController::class, 'solicitarPostagemReversa'])->middleware(['auth', 'verified'])->name('logistica.correios.solicitarPostagemReversa');
Route::get('/logistica/correios/buscarCartao/{contratoSelecionado}',  [LogisticaController::class, 'buscarNumerosCartao'])->middleware(['auth', 'verified'])->name('logistica.correios.buscar-numeros-cartao');
Route::get('/logistica/correios/cancelar/{id}', [LogisticaController::class, 'cancelarPedido'])->middleware(['auth', 'verified'])->name('logistica.correios.cancelar');
Route::get('/logistica/correios/visualizar/{id}', [LogisticaController::class, 'view'])->middleware(['auth', 'verified'])->name('logistica.correios.visualizar');
Route::get('/logistica/correios/rastreio',  function () {return view('logistica.correios.rastreio');} )->middleware(['auth', 'verified'])->name('logistica.correios.rastreio');
Route::post('/logistica/correios/rastrear', [LogisticaController::class,'rastrear'])->middleware(['auth', 'verified'])->name('logistica.correios.rastrear');




Route::get('/logistica/correios/consultarPedido', [LogisticaController::class, 'acompanharPedido'])->middleware(['auth', 'verified'])->name('logistica.correios.consultarPedido');
// Route::get('/estoque/index/historico', [EstoqueController::class, 'getHistorico'])->middleware(['auth', 'verified'])->name('estoque.historico');
// Route::get('/estoque/index/historico/credenciado', [EstoqueController::class, 'getHistoricocredenciado'])->middleware(['auth', 'verified'])->name('estoque.historico.credenciado');
// Route::post('/estoque/create', [EstoqueController::class, 'create'])->middleware(['auth', 'verified'])->name('estoque.create');
// Route::get('/estoque/import', [EstoqueController::class, 'import'])->middleware(['auth', 'verified'])->name('estoque.import');
// Route::post('/estoque/processamento', [EstoqueController::class, 'processamento'])->middleware(['auth', 'verified'])->name('estoque.processamento');
// Route::get('/estoque/edit/{id}', [EstoqueController::class, 'edit'])->middleware(['auth', 'verified'])->name('estoque.edit');
// Route::get('/estoque/excluir/{id}', [EstoqueController::class, 'excluir'])->middleware(['auth', 'verified'])->name('estoque.excluir');
// Route::get('/estoque/historico/{id}', [EstoqueController::class, 'historico'])->middleware(['auth', 'verified'])->name('estoque.historico');


require __DIR__.'/auth.php';
