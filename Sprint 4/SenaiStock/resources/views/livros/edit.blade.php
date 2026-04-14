@extends('layouts.app')

@section('title', 'Editar Livro')

@section('content')
<div style="max-width:600px;margin:0 auto;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('livros.index') }}" style="color:#1a2a5e;text-decoration:none;font-size:20px;">←</a>
        <h1 style="font-size:22px;font-weight:700;color:#1a2a5e;margin:0;">Editar Livro</h1>
    </div>

    <div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08);padding:32px;">
        <form method="POST" action="{{ route('livros.update', $livro) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:16px;">
                <label class="form-label">Título *</label>
                <input type="text" name="titulo" class="form-input"
                       value="{{ old('titulo', $livro->titulo) }}" required>
                @error('titulo') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">ISBN *</label>
                <input type="text" name="isbn" class="form-input"
                       value="{{ old('isbn', $livro->isbn) }}" required>
                @error('isbn') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label class="form-label">Disciplina *</label>
                    <input type="text" name="disciplina" class="form-input"
                           value="{{ old('disciplina', $livro->disciplina) }}" required>
                    @error('disciplina') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Prateleira</label>
                    <input type="text" name="prateleira" class="form-input"
                           value="{{ old('prateleira', $livro->prateleira) }}" placeholder="Ex: A1">
                    @error('prateleira') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-bottom:24px;">
                <label class="form-label">Estoque mínimo *</label>
                <input type="number" name="estoque_minimo" class="form-input"
                       value="{{ old('estoque_minimo', $livro->estoque_minimo) }}" min="1" required>
                <p style="font-size:12px;color:#6b7280;margin:4px 0 0;">
                    Saldo atual: <strong>{{ $livro->quantidade }}</strong> unidade(s) — para alterar o saldo use Entrada/Saída.
                </p>
                @error('estoque_minimo') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('livros.index') }}"
                   style="border:1px solid #d1d5db;background:#fff;border-radius:6px;padding:10px 20px;font-size:14px;color:#374151;text-decoration:none;font-weight:500;">
                    Cancelar
                </a>
                <button type="submit" class="btn-orange" style="padding:10px 24px;font-size:14px;">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
