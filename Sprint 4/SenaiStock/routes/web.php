<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\MovimentacaoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root based on authentication state to avoid redirect loops
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('livros.index')
        : redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    // Livros CRUD
    Route::resource('livros', LivroController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Movimentações (almoxarife)
    Route::post('/livros/{livro}/entrada', [MovimentacaoController::class, 'entrada'])->name('movimentacoes.entrada');
    Route::post('/livros/{livro}/saida', [MovimentacaoController::class, 'saida'])->name('movimentacoes.saida');

    // Reservas (coordenador)
    Route::post('/livros/{livro}/reserva', [MovimentacaoController::class, 'reserva'])->name('movimentacoes.reserva');

    // Confirmar reserva (almoxarife)
    Route::post('/movimentacoes/{movimentacao}/confirmar', [MovimentacaoController::class, 'confirmar'])->name('movimentacoes.confirmar');

    // Reservas (histórico de movimentações)
    Route::get('/reservas', [MovimentacaoController::class, 'index'])->name('reservas.index');

    // Alertas de estoque baixo
    Route::get('/estoque/alertas', [EstoqueController::class, 'alertas'])->name('estoque.alertas');
});
