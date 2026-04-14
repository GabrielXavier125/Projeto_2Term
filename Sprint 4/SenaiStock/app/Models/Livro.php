<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livro extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'isbn',
        'disciplina',
        'prateleira',
        'quantidade',
        'estoque_minimo',
    ];

    protected $casts = [
        'quantidade'    => 'integer',
        'estoque_minimo' => 'integer',
    ];

    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class);
    }

    public function estoqueAbaixoMinimo(): bool
    {
        return $this->quantidade < $this->estoque_minimo;
    }
}
