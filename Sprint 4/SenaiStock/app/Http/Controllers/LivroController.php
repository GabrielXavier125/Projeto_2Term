<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LivroController extends Controller
{
    public function index()
    {
        $livros = Livro::orderBy('titulo')->get();
        return view('livros.index', compact('livros'));
    }

    public function create()
    {
        abort_if(!Auth::user()->isAlmoxarife(), 403, 'Apenas o almoxarife pode cadastrar livros.');
        return view('livros.create');
    }

    public function store(Request $request)
    {
        abort_if(!Auth::user()->isAlmoxarife(), 403);
        $validated = $request->validate([
            'titulo'         => ['required', 'string', 'max:255'],
            'isbn'           => ['required', 'string', 'max:20', 'unique:livros,isbn'],
            'disciplina'     => ['required', 'string', 'max:100'],
            'prateleira'     => ['nullable', 'string', 'max:20'],
            'quantidade'     => ['required', 'integer', 'min:0'],
            'estoque_minimo' => ['required', 'integer', 'min:1'],
        ]);

        Livro::create($validated);

        return redirect()->route('livros.index')
            ->with('success', 'Livro cadastrado com sucesso!');
    }

    public function edit(Livro $livro)
    {
        abort_if(!Auth::user()->isAlmoxarife(), 403, 'Apenas o almoxarife pode editar livros.');
        return view('livros.edit', compact('livro'));
    }

    public function update(Request $request, Livro $livro)
    {
        abort_if(!Auth::user()->isAlmoxarife(), 403);
        $validated = $request->validate([
            'titulo'         => ['required', 'string', 'max:255'],
            'isbn'           => ['required', 'string', 'max:20', 'unique:livros,isbn,' . $livro->id],
            'disciplina'     => ['required', 'string', 'max:100'],
            'prateleira'     => ['nullable', 'string', 'max:20'],
            'estoque_minimo' => ['required', 'integer', 'min:1'],
        ]);

        $livro->update($validated);

        return redirect()->route('livros.index')
            ->with('success', 'Livro atualizado com sucesso!');
    }

    public function destroy(Livro $livro)
    {
        abort_if(!Auth::user()->isAlmoxarife(), 403);
        $livro->delete();
        return redirect()->route('livros.index')
            ->with('success', 'Livro removido com sucesso!');
    }
}
