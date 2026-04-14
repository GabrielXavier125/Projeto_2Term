@extends('layouts.app')

@section('title', 'Biblioteca SENAI')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;color:#1a2a5e;margin:0;">📚 Biblioteca SENAI</h1>
    @if(auth()->user()->isAlmoxarife())
        <a href="{{ route('livros.create') }}" class="btn-orange">+ Adicionar Livro</a>
    @endif
</div>

@if(session('success'))
    <div class="alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error" style="margin-bottom:16px;">
        @foreach($errors->all() as $error){{ $error }}<br>@endforeach
    </div>
@endif

<!-- Tabela de livros -->
<div class="table-container">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr class="table-header">
                @if(auth()->user()->podeReservar())
                <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;width:36px;">
                    <input type="checkbox" id="select-all" style="cursor:pointer;">
                </th>
                @endif
                <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">ISBN</th>
                <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">Nome do Livro</th>
                <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">Disciplina</th>
                <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">Prateleira</th>
                <th style="padding:14px 16px;text-align:center;font-size:13px;font-weight:600;">Qtd.</th>
                <th style="padding:14px 16px;text-align:center;font-size:13px;font-weight:600;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($livros as $index => $livro)
            <tr class="{{ $index % 2 === 0 ? 'table-row-odd' : 'table-row-even' }} livro-row"
                data-id="{{ $livro->id }}"
                @if(auth()->user()->podeReservar()) style="cursor:pointer;transition:background 0.15s;" ondblclick="toggleSelect(this)" @endif>
                @if(auth()->user()->podeReservar())
                <td style="padding:12px 16px;text-align:center;">
                    <input type="checkbox" class="livro-check" value="{{ $livro->id }}"
                           onclick="event.stopPropagation();" style="cursor:pointer;">
                </td>
                @endif
                <td style="padding:12px 16px;font-size:14px;color:#374151;">{{ $livro->isbn }}</td>
                <td style="padding:12px 16px;font-size:14px;font-weight:500;color:#111827;">{{ $livro->titulo }}</td>
                <td style="padding:12px 16px;font-size:14px;color:#6b7280;">{{ $livro->disciplina }}</td>
                <td style="padding:12px 16px;font-size:14px;color:#6b7280;">{{ $livro->prateleira ?: '—' }}</td>
                <td style="padding:12px 16px;text-align:center;font-size:14px;font-weight:700;"
                    class="{{ $livro->estoqueAbaixoMinimo() ? 'qty-low' : 'text-green' }}">
                    {{ $livro->quantidade }}
                    @if($livro->estoqueAbaixoMinimo())
                        <span class="badge-low">baixo</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;white-space:nowrap;">
                    @if(auth()->user()->isAlmoxarife())
                        <button title="Entrada de estoque"
                                onclick="event.stopPropagation();openEntrada({{ $livro->id }}, '{{ addslashes($livro->titulo) }}', {{ $livro->quantidade }})"
                                style="background:none;border:none;cursor:pointer;color:#16a34a;font-size:18px;padding:4px 5px;">⬆</button>
                        <button title="Saída de estoque"
                                onclick="event.stopPropagation();openSaida({{ $livro->id }}, '{{ addslashes($livro->titulo) }}', {{ $livro->quantidade }})"
                                style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:18px;padding:4px 5px;">⬇</button>
                        <a href="{{ route('livros.edit', $livro) }}" title="Editar"
                           onclick="event.stopPropagation();"
                           style="color:#1a2a5e;font-size:18px;padding:4px 5px;text-decoration:none;">✏️</a>
                        <button title="Excluir" onclick="event.stopPropagation();confirmDelete({{ $livro->id }}, '{{ addslashes($livro->titulo) }}')"
                                style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:18px;padding:4px 5px;">🗑️</button>
                    @else
                        <button title="Fazer reserva"
                                onclick="event.stopPropagation();openReserva({{ $livro->id }}, '{{ addslashes($livro->titulo) }}', {{ $livro->quantidade }})"
                                style="background:none;border:none;cursor:pointer;color:#1a2a5e;font-size:18px;padding:4px 5px;"
                                {{ $livro->quantidade <= 0 ? 'disabled' : '' }}>🔖</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding:40px;text-align:center;color:#9ca3af;font-size:14px;">
                    Nenhum livro cadastrado.
                    @if(auth()->user()->isAlmoxarife())
                        <a href="{{ route('livros.create') }}" style="color:#1a2a5e;font-weight:600;">Adicionar o primeiro livro</a>
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(auth()->user()->podeReservar())
<div style="margin-top:16px;background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:10px 16px;font-size:13px;color:#92400e;">
    💡 <strong>Dica:</strong> Clique duas vezes em qualquer linha para selecionar, ou clique no ícone 🔖 para reservar diretamente.
</div>
<div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;">
    <button id="btn-reservar" class="btn-navy" onclick="abrirReservaLote()" disabled
            style="opacity:0.5;cursor:not-allowed;">
        🔖 Reservar Selecionados
    </button>
    <span id="sel-count" style="color:#6b7280;font-size:13px;">✔ 0 livro(s) selecionado(s)</span>
</div>
@endif

@if(auth()->user()->isAlmoxarife())
<!-- Modal Excluir -->
<div id="modal-delete" style="display:none;" class="modal-overlay" onclick="closeModal('modal-delete')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3 style="margin:0 0 12px;color:#1a2a5e;font-size:18px;">Confirmar exclusão</h3>
        <p style="color:#374151;font-size:14px;margin:0 0 24px;">Deseja realmente excluir o livro <strong id="delete-title"></strong>?</p>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="closeModal('modal-delete')"
                    style="border:1px solid #d1d5db;background:#fff;border-radius:6px;padding:8px 18px;cursor:pointer;font-size:14px;">Cancelar</button>
            <form id="form-delete" method="POST" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger" style="padding:8px 18px;border-radius:6px;font-size:14px;">Excluir</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Entrada -->
<div id="modal-entrada" style="display:none;" class="modal-overlay" onclick="closeModal('modal-entrada')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3 style="margin:0 0 4px;color:#1a2a5e;font-size:18px;">⬆ Entrada de Estoque</h3>
        <p id="entrada-title" style="color:#6b7280;font-size:13px;margin:0 0 20px;"></p>
        <form id="form-entrada" method="POST">
            @csrf
            <label class="form-label">Quantidade *</label>
            <input type="number" name="quantidade" min="1" class="form-input" required placeholder="Ex: 30">
            <label class="form-label" style="margin-top:12px;">Observação</label>
            <textarea name="observacao" class="form-input" rows="2" placeholder="Ex: Recebido da editora"></textarea>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" onclick="closeModal('modal-entrada')"
                        style="border:1px solid #d1d5db;background:#fff;border-radius:6px;padding:8px 18px;cursor:pointer;font-size:14px;">Cancelar</button>
                <button type="submit" class="btn-navy" style="padding:8px 20px;">Confirmar Entrada</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Saída -->
<div id="modal-saida" style="display:none;" class="modal-overlay" onclick="closeModal('modal-saida')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3 style="margin:0 0 4px;color:#dc2626;font-size:18px;">⬇ Saída de Estoque</h3>
        <p id="saida-title" style="color:#6b7280;font-size:13px;margin:0 0 20px;"></p>
        <form id="form-saida" method="POST">
            @csrf
            <label class="form-label">Quantidade *</label>
            <input type="number" name="quantidade" min="1" id="saida-qty" class="form-input" required placeholder="Ex: 30">
            <label class="form-label" style="margin-top:12px;">Turma</label>
            <input type="text" name="turma" class="form-input" placeholder="Ex: Turma A — Eletrotécnica">
            <label class="form-label" style="margin-top:12px;">Observação</label>
            <textarea name="observacao" class="form-input" rows="2" placeholder="Ex: Retirado pelo instrutor"></textarea>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" onclick="closeModal('modal-saida')"
                        style="border:1px solid #d1d5db;background:#fff;border-radius:6px;padding:8px 18px;cursor:pointer;font-size:14px;">Cancelar</button>
                <button type="submit" class="btn-danger" style="border-radius:6px;padding:8px 20px;font-size:14px;">Confirmar Saída</button>
            </div>
        </form>
    </div>
</div>
@endif

@if(auth()->user()->podeReservar())
<!-- Modal Reserva -->
<div id="modal-reserva" style="display:none;" class="modal-overlay" onclick="closeModal('modal-reserva')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3 style="margin:0 0 4px;color:#1a2a5e;font-size:18px;">🔖 Fazer Reserva</h3>
        <p id="reserva-title" style="color:#6b7280;font-size:13px;margin:0 0 20px;"></p>
        <form id="form-reserva" method="POST">
            @csrf
            <label class="form-label">Quantidade *</label>
            <input type="number" name="quantidade" min="1" id="reserva-qty" class="form-input" required placeholder="Ex: 30">
            <label class="form-label" style="margin-top:12px;">Turma</label>
            <input type="text" name="turma" class="form-input" placeholder="Ex: Turma A — Eletrotécnica">
            <label class="form-label" style="margin-top:12px;">Observação</label>
            <textarea name="observacao" class="form-input" rows="2" placeholder="Ex: Uso nas aulas de segunda"></textarea>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" onclick="closeModal('modal-reserva')"
                        style="border:1px solid #d1d5db;background:#fff;border-radius:6px;padding:8px 18px;cursor:pointer;font-size:14px;">Cancelar</button>
                <button type="submit" class="btn-navy" style="padding:8px 20px;">Confirmar Reserva</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            ['modal-delete','modal-entrada','modal-saida','modal-reserva'].forEach(id => {
                const el = document.getElementById(id); if (el) closeModal(id);
            });
        }
    });

    @if(auth()->user()->isAlmoxarife())
    function confirmDelete(id, title) {
        document.getElementById('delete-title').textContent = title;
        document.getElementById('form-delete').action = `/livros/${id}`;
        openModal('modal-delete');
    }

    function openEntrada(id, title, qty) {
        document.getElementById('entrada-title').textContent = `${title} — Saldo atual: ${qty} un.`;
        document.getElementById('form-entrada').action = `/livros/${id}/entrada`;
        openModal('modal-entrada');
    }

    function openSaida(id, title, qty) {
        document.getElementById('saida-title').textContent = `${title} — Saldo atual: ${qty} un.`;
        document.getElementById('saida-qty').max = qty;
        document.getElementById('form-saida').action = `/livros/${id}/saida`;
        openModal('modal-saida');
    }
    @endif

    @if(auth()->user()->podeReservar())
    function openReserva(id, title, qty) {
        document.getElementById('reserva-title').textContent = `${title} — Saldo disponível: ${qty} un.`;
        document.getElementById('reserva-qty').max = qty;
        document.getElementById('form-reserva').action = `/livros/${id}/reserva`;
        openModal('modal-reserva');
    }

    function toggleSelect(row) {
        const check = row.querySelector('.livro-check');
        check.checked = !check.checked;
        row.classList.toggle('table-row-selected', check.checked);
        updateCount();
    }

    document.querySelectorAll('.livro-check').forEach(cb => {
        cb.addEventListener('change', function () {
            this.closest('tr').classList.toggle('table-row-selected', this.checked);
            updateCount();
        });
    });

    document.getElementById('select-all').addEventListener('change', function () {
        document.querySelectorAll('.livro-check').forEach(cb => {
            cb.checked = this.checked;
            cb.closest('tr').classList.toggle('table-row-selected', this.checked);
        });
        updateCount();
    });

    function updateCount() {
        const n = document.querySelectorAll('.livro-check:checked').length;
        document.getElementById('sel-count').textContent = `✔ ${n} livro(s) selecionado(s)`;
        const btn = document.getElementById('btn-reservar');
        btn.disabled = n === 0;
        btn.style.opacity = n > 0 ? '1' : '0.5';
        btn.style.cursor = n > 0 ? 'pointer' : 'not-allowed';
    }

    function abrirReservaLote() {
        const ids = [...document.querySelectorAll('.livro-check:checked')].map(cb => cb.value);
        if (ids.length === 0) return;
        // Para lote, abre com o primeiro selecionado
        const firstRow = document.querySelector(`.livro-check[value="${ids[0]}"]`).closest('tr');
        const titulo = firstRow.querySelector('td:nth-child(3)').textContent.trim();
        const qty = parseInt(firstRow.querySelector('td:nth-child(6)').textContent.trim());
        openReserva(ids[0], titulo, qty);
    }
    @endif
</script>
@endpush
