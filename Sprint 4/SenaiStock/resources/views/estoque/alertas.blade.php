@extends('layouts.app')

@section('title', 'Alertas de Estoque')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;color:#1a2a5e;margin:0;">⚠️ Alertas de Estoque Baixo</h1>
    <a href="{{ route('livros.index') }}" class="btn-navy" style="font-size:13px;padding:8px 14px;">← Voltar à Biblioteca</a>
</div>

@if($livros->isEmpty())
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:24px;border-radius:12px;text-align:center;font-size:15px;">
        ✅ Todos os livros estão com estoque adequado!
    </div>
@else
    <div class="alert-warning" style="margin-bottom:20px;">
        ⚠️ <strong>{{ $livros->count() }} título(s)</strong> com estoque abaixo do mínimo definido.
    </div>

    <div class="table-container">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr class="table-header">
                    <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">Livro</th>
                    <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">Disciplina</th>
                    <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">Prateleira</th>
                    <th style="padding:14px 16px;text-align:center;font-size:13px;font-weight:600;">Saldo Atual</th>
                    <th style="padding:14px 16px;text-align:center;font-size:13px;font-weight:600;">Mínimo</th>
                    <th style="padding:14px 16px;text-align:center;font-size:13px;font-weight:600;">Déficit</th>
                    <th style="padding:14px 16px;text-align:center;font-size:13px;font-weight:600;">Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($livros as $index => $livro)
                <tr class="{{ $index % 2 === 0 ? 'table-row-odd' : 'table-row-even' }}">
                    <td style="padding:12px 16px;">
                        <div style="font-size:14px;font-weight:600;color:#111827;">{{ $livro->titulo }}</div>
                        <div style="font-size:11px;color:#9ca3af;">{{ $livro->isbn }}</div>
                    </td>
                    <td style="padding:12px 16px;font-size:14px;color:#6b7280;">{{ $livro->disciplina }}</td>
                    <td style="padding:12px 16px;font-size:14px;color:#6b7280;">{{ $livro->prateleira ?: '—' }}</td>
                    <td style="padding:12px 16px;text-align:center;">
                        <span style="background:#fee2e2;color:#dc2626;font-size:14px;font-weight:700;padding:3px 12px;border-radius:20px;">
                            {{ $livro->quantidade }}
                        </span>
                    </td>
                    <td style="padding:12px 16px;text-align:center;font-size:14px;color:#6b7280;font-weight:600;">
                        {{ $livro->estoque_minimo }}
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        <span style="background:#fef3c7;color:#92400e;font-size:13px;font-weight:700;padding:3px 10px;border-radius:20px;">
                            −{{ $livro->estoque_minimo - $livro->quantidade }}
                        </span>
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        <a href="{{ route('livros.index') }}"
                           onclick="sessionStorage.setItem('openEntrada', JSON.stringify({id:{{ $livro->id }},titulo:'{{ addslashes($livro->titulo) }}',qty:{{ $livro->quantidade }}}))"
                           style="color:#1a2a5e;font-size:13px;font-weight:600;text-decoration:none;border:1px solid #1a2a5e;padding:4px 12px;border-radius:6px;">
                            ⬆ Repor
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
