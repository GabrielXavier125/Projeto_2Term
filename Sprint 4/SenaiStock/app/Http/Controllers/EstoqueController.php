<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use Illuminate\Support\Facades\Auth;

class EstoqueController extends Controller
{
    public function alertas()
    {
        abort_if(!Auth::user()->isAlmoxarife(), 403, 'Apenas o almoxarife tem acesso aos alertas de estoque.');
        $livros = Livro::whereColumn('quantidade', '<', 'estoque_minimo')
            ->orderBy('quantidade')
            ->get();

        return view('estoque.alertas', compact('livros'));
    }
}
