<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CredenciadoController;
use App\Http\Controllers\AbastecimentoImpressaoController;
use Illuminate\Support\Facades\Route;

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

Route::get('/home', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('home');

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
Route::put('/credenciado/atualizar/{id}', [CredenciadoController::class, 'update'])->middleware(['auth', 'verified'])->name('credenciado.atualizar');

// Rotas impressão de cartões
Route::get('/abastecimento/impressao/index', [AbastecimentoImpressaoController::class, 'index'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.index');
Route::get('/abastecimento/impressao/create', [AbastecimentoImpressaoController::class, 'importar'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.importar');
Route::post('/abastecimento/impressao/processamento', [AbastecimentoImpressaoController::class, 'processamento'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.processamento');
Route::get('/abastecimento/impressao/edit/{id}', [AbastecimentoImpressaoController::class, 'Editar_lote'])->middleware(['auth', 'verified'])->name('.abastecimento.impressao.edit');
Route::get('/abastecimento/impressao/edit/cartao/{id}', [AbastecimentoImpressaoController::class, 'edit_cartao'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit.cartao');
Route::get('/abastecimento/impressao/edit/status/{id}', [AbastecimentoImpressaoController::class, 'edit_status'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.edit.status');
Route::get('/abastecimento/impressao/lote/exluir/{id}', [AbastecimentoImpressaoController::class, 'excluir_lote'])->middleware(['auth', 'verified'])->name('abastecimento.impressao.lote.excluir');
require __DIR__.'/auth.php';
