<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livro;
use App\Models\Movimentacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovimentacaoApiController extends Controller
{
    public function entrada(Request $request, Livro $livro)
    {
        $validated = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        $movimentacao = DB::transaction(function () use ($livro, $validated) {
            $livro->increment('quantidade', $validated['quantidade']);

            return Movimentacao::create([
                'livro_id'   => $livro->id,
                'user_id'    => Auth::id(),
                'tipo'       => 'entrada',
                'quantidade' => $validated['quantidade'],
                'observacao' => $validated['observacao'] ?? null,
            ]);
        });

        $livro->refresh();

        return response()->json([
            'message'        => 'Entrada registrada com sucesso.',
            'movimentacao'   => $movimentacao,
            'saldo_atual'    => $livro->quantidade,
        ], 201);
    }

    public function saida(Request $request, Livro $livro)
    {
        $validated = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
            'turma'      => ['nullable', 'string', 'max:100'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['quantidade'] > $livro->quantidade) {
            return response()->json([
                'message'     => 'Estoque insuficiente.',
                'saldo_atual' => $livro->quantidade,
            ], 422);
        }

        $movimentacao = DB::transaction(function () use ($livro, $validated) {
            $livro->decrement('quantidade', $validated['quantidade']);

            return Movimentacao::create([
                'livro_id'   => $livro->id,
                'user_id'    => Auth::id(),
                'tipo'       => 'saida',
                'quantidade' => $validated['quantidade'],
                'turma'      => $validated['turma'] ?? null,
                'observacao' => $validated['observacao'] ?? null,
            ]);
        });

        $livro->refresh();

        return response()->json([
            'message'      => 'Saída registrada com sucesso.',
            'movimentacao' => $movimentacao,
            'saldo_atual'  => $livro->quantidade,
        ], 201);
    }

    public function historico(Livro $livro)
    {
        $movimentacoes = $livro->movimentacoes()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($movimentacoes);
    }
}
