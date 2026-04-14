<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\LivroApiController;
use App\Http\Controllers\Api\MovimentacaoApiController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/auth/login', [AuthApiController::class, 'login'])->name('api.auth.login');

// Protected API routes (Sanctum token)
Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::post('/auth/logout', [AuthApiController::class, 'logout'])->name('auth.logout');

    // Livros
    Route::apiResource('livros', LivroApiController::class);
    Route::get('/estoque/baixo', [LivroApiController::class, 'estoqueBaixo'])->name('estoque.baixo');

    // Movimentações
    Route::post('/livros/{livro}/entrada', [MovimentacaoApiController::class, 'entrada'])->name('livros.entrada');
    Route::post('/livros/{livro}/saida', [MovimentacaoApiController::class, 'saida'])->name('livros.saida');
    Route::get('/livros/{livro}/historico', [MovimentacaoApiController::class, 'historico'])->name('livros.historico');
});
