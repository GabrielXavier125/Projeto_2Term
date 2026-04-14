<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use App\Models\Movimentacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovimentacaoController extends Controller
{
    public function index()
    {
        $query = Movimentacao::with(['livro', 'user', 'confirmadoPor'])
            ->orderByDesc('created_at');

        // Coordenador vê apenas suas próprias reservas
        if (Auth::user()->isCoordenador()) {
            $query->where('user_id', Auth::id());
        }

        $movimentacoes = $query->paginate(20);
        $livros = Livro::orderBy('titulo')->get();

        return view('reservas.index', compact('movimentacoes', 'livros'));
    }

    /** Entrada de estoque — somente almoxarife */
    public function entrada(Request $request, Livro $livro)
    {
        abort_if(!Auth::user()->isAlmoxarife(), 403, 'Apenas o almoxarife pode registrar entradas.');

        $validated = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($livro, $validated) {
            $livro->increment('quantidade', $validated['quantidade']);

            Movimentacao::create([
                'livro_id'   => $livro->id,
                'user_id'    => Auth::id(),
                'tipo'       => 'entrada',
                'status'     => 'confirmada',
                'quantidade' => $validated['quantidade'],
                'observacao' => $validated['observacao'] ?? null,
            ]);
        });

        return redirect()->back()
            ->with('success', "Entrada de {$validated['quantidade']} unidade(s) registrada para \"{$livro->titulo}\".");
    }

    /** Saída direta de estoque — somente almoxarife */
    public function saida(Request $request, Livro $livro)
    {
        abort_if(!Auth::user()->isAlmoxarife(), 403, 'Apenas o almoxarife pode registrar saídas diretas.');

        $validated = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
            'turma'      => ['nullable', 'string', 'max:100'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['quantidade'] > $livro->quantidade) {
            return redirect()->back()
                ->withErrors(['quantidade' => "Estoque insuficiente. Saldo atual: {$livro->quantidade} unidade(s)."])
                ->withInput();
        }

        DB::transaction(function () use ($livro, $validated) {
            $livro->decrement('quantidade', $validated['quantidade']);

            Movimentacao::create([
                'livro_id'   => $livro->id,
                'user_id'    => Auth::id(),
                'tipo'       => 'saida',
                'status'     => 'confirmada',
                'quantidade' => $validated['quantidade'],
                'turma'      => $validated['turma'] ?? null,
                'observacao' => $validated['observacao'] ?? null,
            ]);
        });

        return redirect()->back()
            ->with('success', "Saída de {$validated['quantidade']} unidade(s) registrada para \"{$livro->titulo}\".");
    }

    /** Reserva — coordenador */
    public function reserva(Request $request, Livro $livro)
    {
        abort_if(Auth::user()->isAlmoxarife(), 403, 'O almoxarife não realiza reservas. Use saída direta.');

        $validated = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
            'turma'      => ['nullable', 'string', 'max:100'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['quantidade'] > $livro->quantidade) {
            return redirect()->back()
                ->withErrors(['quantidade' => "Estoque insuficiente. Saldo atual: {$livro->quantidade} unidade(s)."])
                ->withInput();
        }

        Movimentacao::create([
            'livro_id'   => $livro->id,
            'user_id'    => Auth::id(),
            'tipo'       => 'reserva',
            'status'     => 'pendente',
            'quantidade' => $validated['quantidade'],
            'turma'      => $validated['turma'] ?? null,
            'observacao' => $validated['observacao'] ?? null,
        ]);

        return redirect()->route('reservas.index')
            ->with('success', "Reserva de \"{$livro->titulo}\" registrada. Aguardando confirmação do almoxarife.");
    }

    /** Confirmar reserva pendente — somente almoxarife */
    public function confirmar(Movimentacao $movimentacao)
    {
        abort_if(!Auth::user()->isAlmoxarife(), 403, 'Apenas o almoxarife pode confirmar reservas.');
        abort_if(!$movimentacao->isPendente(), 422, 'Esta reserva já foi processada.');

        DB::transaction(function () use ($movimentacao) {
            if ($movimentacao->quantidade > $movimentacao->livro->quantidade) {
                abort(422, "Estoque insuficiente para confirmar. Saldo atual: {$movimentacao->livro->quantidade} unidade(s).");
            }

            $movimentacao->livro->decrement('quantidade', $movimentacao->quantidade);

            $movimentacao->update([
                'status'        => 'confirmada',
                'confirmado_por' => Auth::id(),
                'confirmado_at'  => now(),
            ]);
        });

        return redirect()->back()
            ->with('success', "Reserva de \"{$movimentacao->livro->titulo}\" confirmada. Estoque atualizado.");
    }
}
