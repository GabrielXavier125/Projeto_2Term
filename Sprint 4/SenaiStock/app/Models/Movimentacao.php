<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes';

    protected $fillable = [
        'livro_id',
        'user_id',
        'tipo',
        'status',
        'quantidade',
        'turma',
        'observacao',
        'confirmado_por',
        'confirmado_at',
    ];

    protected $casts = [
        'quantidade'    => 'integer',
        'confirmado_at' => 'datetime',
    ];

    public function livro()
    {
        return $this->belongsTo(Livro::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function confirmadoPor()
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }

    public function isPendente(): bool { return $this->status === 'pendente'; }
}
