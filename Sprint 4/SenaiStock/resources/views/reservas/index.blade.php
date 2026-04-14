@extends('layouts.app')

@section('title', 'Reservas / Movimentações')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 style="font-size:24px;font-weight:700;color:#1a2a5e;margin:0;">📋 Reservas e Movimentações</h1>
    @if(auth()->user()->isAlmoxarife())
    <div style="display:flex;gap:10px;">
        <button onclick="openModal('modal-entrada-rapida')" class="btn-navy" style="font-size:13px;padding:8px 14px;">⬆ Entrada</button>
        <button onclick="openModal('modal-saida-rapida')" class="btn-orange" style="font-size:13px;padding:8px 14px;">⬇ Saída</button>
    </div>
    @endif
</div>

@if(session('success'))
    <div class="alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>
@endif

<!-- Tabela de movimentações -->
<div class="table-container">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr class="table-header">
                <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">Data</th>
                <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">Livro</th>
                <th style="padding:14px 16px;text-align:center;font-size:13px;font-weight:600;">Tipo</th>
                <th style="padding:14px 16px;text-align:center;font-size:13px;font-weight:600;">Status</th>
                <th style="padding:14px 16px;text-align:center;font-size:13px;font-weight:600;">Qtd.</th>
                <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">Turma</th>
                <th style="padding:14px 16px;text-align:left;font-size:13px;font-weight:600;">Solicitante</th>
                @if(auth()->user()->isAlmoxarife())
                <th style="padding:14px 16px;text-align:center;font-size:13px;font-weight:600;">Ação</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($movimentacoes as $index => $mov)
            <tr class="{{ $index % 2 === 0 ? 'table-row-odd' : 'table-row-even' }}">
                <td style="padding:12px 16px;font-size:13px;color:#6b7280;white-space:nowrap;">
                    {{ $mov->created_at->format('d/m/Y H:i') }}
                </td>
                <td style="padding:12px 16px;font-size:14px;font-weight:500;color:#111827;">
                    {{ $mov->livro->titulo }}
                    <div style="font-size:11px;color:#9ca3af;">{{ $mov->livro->isbn }}</div>
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($mov->tipo === 'entrada')
                        <span style="background:#d1fae5;color:#065f46;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">⬆ ENTRADA</span>
                    @elseif($mov->tipo === 'saida')
                        <span style="background:#fee2e2;color:#991b1b;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">⬇ SAÍDA</span>
                    @else
                        <span style="background:#ede9fe;color:#5b21b6;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">🔖 RESERVA</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($mov->status === 'pendente')
                        <span style="background:#fef3c7;color:#92400e;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">⏳ PENDENTE</span>
                    @else
                        <span style="background:#d1fae5;color:#065f46;font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;">✔ CONFIRMADA</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;font-size:14px;font-weight:700;
                           color:{{ $mov->tipo === 'entrada' ? '#16a34a' : ($mov->tipo === 'reserva' ? '#7c3aed' : '#dc2626') }};">
                    {{ $mov->tipo === 'entrada' ? '+' : '' }}{{ $mov->quantidade }}
                </td>
                <td style="padding:12px 16px;font-size:13px;color:#374151;">{{ $mov->turma ?: '—' }}</td>
                <td style="padding:12px 16px;font-size:13px;color:#374151;">
                    {{ $mov->user->name }}
                    @if($mov->status === 'confirmada' && $mov->confirmadoPor)
                        <div style="font-size:11px;color:#9ca3af;">
                            Confirmado por {{ $mov->confirmadoPor->name }}<br>
                            {{ $mov->confirmado_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </td>
                @if(auth()->user()->isAlmoxarife())
                <td style="padding:12px 16px;text-align:center;">
                    @if($mov->isPendente())
                        <form method="POST" action="{{ route('movimentacoes.confirmar', $mov) }}" style="margin:0;">
                            @csrf
                            <button type="submit" title="Confirmar saída"
                                    style="background:#16a34a;color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;">
                                ✔ Confirmar
                            </button>
                        </form>
                    @else
                        <span style="color:#9ca3af;font-size:12px;">—</span>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ auth()->user()->isAlmoxarife() ? 8 : 7 }}"
                    style="padding:40px;text-align:center;color:#9ca3af;font-size:14px;">
                    Nenhuma movimentação registrada ainda.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginação -->
<div style="margin-top:16px;">
    {{ $movimentacoes->links() }}
</div>

@if(auth()->user()->isAlmoxarife())
<!-- Modal Entrada Rápida -->
<div id="modal-entrada-rapida" style="display:none;" class="modal-overlay" onclick="closeModal('modal-entrada-rapida')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3 style="margin:0 0 4px;color:#1a2a5e;font-size:18px;">⬆ Entrada de Estoque</h3>
        <p style="color:#6b7280;font-size:13px;margin:0 0 20px;">Registrar chegada de livros</p>
        <form id="form-entrada-rapida" method="POST">
            @csrf
            <label class="form-label">Livro *</label>
            <select name="_livro_id" id="sel-entrada-livro" class="form-input" required onchange="updateEntradaAction(this.value)">
                <option value="">Selecione o livro...</option>
                @foreach($livros as $livro)
                    <option value="{{ $livro->id }}">{{ $livro->titulo }} (Saldo: {{ $livro->quantidade }})</option>
                @endforeach
            </select>
            <label class="form-label" style="margin-top:12px;">Quantidade *</label>
            <input type="number" name="quantidade" min="1" class="form-input" required placeholder="Ex: 30">
            <label class="form-label" style="margin-top:12px;">Observação</label>
            <textarea name="observacao" class="form-input" rows="2" placeholder="Ex: Recebido da editora"></textarea>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" onclick="closeModal('modal-entrada-rapida')"
                        style="border:1px solid #d1d5db;background:#fff;border-radius:6px;padding:8px 18px;cursor:pointer;font-size:14px;">Cancelar</button>
                <button type="submit" class="btn-navy" style="padding:8px 20px;">Confirmar Entrada</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Saída Rápida -->
<div id="modal-saida-rapida" style="display:none;" class="modal-overlay" onclick="closeModal('modal-saida-rapida')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3 style="margin:0 0 4px;color:#dc2626;font-size:18px;">⬇ Saída de Estoque</h3>
        <p style="color:#6b7280;font-size:13px;margin:0 0 20px;">Registrar retirada de livros</p>
        <form id="form-saida-rapida" method="POST">
            @csrf
            <label class="form-label">Livro *</label>
            <select name="_livro_id" id="sel-saida-livro" class="form-input" required onchange="updateSaidaAction(this.value)">
                <option value="">Selecione o livro...</option>
                @foreach($livros as $livro)
                    <option value="{{ $livro->id }}" data-qty="{{ $livro->quantidade }}">
                        {{ $livro->titulo }} (Saldo: {{ $livro->quantidade }})
                    </option>
                @endforeach
            </select>
            <label class="form-label" style="margin-top:12px;">Quantidade *</label>
            <input type="number" name="quantidade" min="1" id="saida-rapida-qty" class="form-input" required placeholder="Ex: 30">
            <label class="form-label" style="margin-top:12px;">Turma</label>
            <input type="text" name="turma" class="form-input" placeholder="Ex: Turma A — Eletrotécnica">
            <label class="form-label" style="margin-top:12px;">Observação</label>
            <textarea name="observacao" class="form-input" rows="2" placeholder="Ex: Retirado pelo instrutor"></textarea>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" onclick="closeModal('modal-saida-rapida')"
                        style="border:1px solid #d1d5db;background:#fff;border-radius:6px;padding:8px 18px;cursor:pointer;font-size:14px;">Cancelar</button>
                <button type="submit" class="btn-danger" style="border-radius:6px;padding:8px 20px;font-size:14px;">Confirmar Saída</button>
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
            ['modal-entrada-rapida','modal-saida-rapida'].forEach(id => {
                const el = document.getElementById(id); if (el) closeModal(id);
            });
        }
    });

    @if(auth()->user()->isAlmoxarife())
    function updateEntradaAction(livroId) {
        document.getElementById('form-entrada-rapida').action = `/livros/${livroId}/entrada`;
    }

    function updateSaidaAction(livroId) {
        document.getElementById('form-saida-rapida').action = `/livros/${livroId}/saida`;
        const opt = document.querySelector(`#sel-saida-livro option[value="${livroId}"]`);
        if (opt) document.getElementById('saida-rapida-qty').max = opt.dataset.qty;
    }
    @endif
</script>
@endpush
