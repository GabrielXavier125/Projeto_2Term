<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livro;
use Illuminate\Http\Request;

class LivroApiController extends Controller
{
    public function index()
    {
        return response()->json(Livro::orderBy('titulo')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo'         => ['required', 'string', 'max:255'],
            'isbn'           => ['required', 'string', 'max:20', 'unique:livros,isbn'],
            'disciplina'     => ['required', 'string', 'max:100'],
            'prateleira'     => ['nullable', 'string', 'max:20'],
            'quantidade'     => ['required', 'integer', 'min:0'],
            'estoque_minimo' => ['required', 'integer', 'min:1'],
        ]);

        $livro = Livro::create($validated);

        return response()->json($livro, 201);
    }

    public function show(Livro $livro)
    {
        return response()->json($livro);
    }

    public function update(Request $request, Livro $livro)
    {
        $validated = $request->validate([
            'titulo'         => ['sometimes', 'required', 'string', 'max:255'],
            'isbn'           => ['sometimes', 'required', 'string', 'max:20', 'unique:livros,isbn,' . $livro->id],
            'disciplina'     => ['sometimes', 'required', 'string', 'max:100'],
            'prateleira'     => ['nullable', 'string', 'max:20'],
            'estoque_minimo' => ['sometimes', 'required', 'integer', 'min:1'],
        ]);

        $livro->update($validated);

        return response()->json($livro);
    }

    public function destroy(Livro $livro)
    {
        $livro->delete();
        return response()->json(['message' => 'Livro removido com sucesso.']);
    }

    public function estoqueBaixo()
    {
        $livros = Livro::whereColumn('quantidade', '<', 'estoque_minimo')
            ->orderBy('quantidade')
            ->get();

        return response()->json([
            'total'  => $livros->count(),
            'livros' => $livros,
        ]);
    }
}
