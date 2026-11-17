@if($pedido->pode_solicitar_cancelamento)
    <form action="{{ route('pedidos.solicitar-cancelamento', $pedido->id) }}" method="POST">
        @csrf
        <input type="hidden" name="motivo" value="">
        <button type="button" onclick="solicitarCancelamento(this.form, '{{ $pedido->codigo }}')"
                class="flex items-center justify-center gap-2 w-full border-2 border-red-200 text-red-500 hover:bg-red-500 hover:border-red-500 hover:text-white text-xs font-bold uppercase tracking-widest py-2.5 rounded-xl transition">
            <i class="fas fa-undo"></i> Solicitar cancelamento
        </button>
    </form>
@elseif($pedido->cancelamento === 'solicitado')
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs">
        <span class="block font-bold text-amber-700"><i class="fas fa-hourglass-half mr-1"></i> Cancelamento solicitado</span>
        <span class="block text-amber-700/80 mt-1">Aguardando análise da loja{{ $pedido->cancelamento_solicitado_em ? ' · enviado em ' . $pedido->cancelamento_solicitado_em->timezone('America/Sao_Paulo')->format('d/m/Y H:i') : '' }}.</span>
        @if($pedido->cancelamento_motivo)
            <span class="block text-gray-500 mt-2 italic">"{{ $pedido->cancelamento_motivo }}"</span>
        @endif
    </div>
@elseif($pedido->cancelamento === 'recusado')
    <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-xs">
        <span class="block font-bold text-red-700"><i class="fas fa-ban mr-1"></i> Cancelamento recusado</span>
        <span class="block text-red-700/80 mt-1">A loja analisou e não aprovou o cancelamento deste pedido.</span>
    </div>
@elseif($pedido->cancelamento === 'admin')
    <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 text-xs">
        <span class="block font-bold text-purple-700"><i class="fas fa-ban mr-1"></i> Pedido cancelado pela loja</span>
        @if($pedido->cancelamento_motivo)
            <span class="block text-gray-600 mt-1">Motivo: "{{ $pedido->cancelamento_motivo }}"</span>
        @endif
        <span class="block text-purple-700/80 mt-1">O valor de R$ {{ number_format($pedido->total_reais, 2, ',', '.') }} foi reembolsado.</span>
    </div>
@elseif($pedido->cancelamento === 'aprovado')
    <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 text-xs">
        <span class="block font-bold text-purple-700"><i class="fas fa-check-circle mr-1"></i> Cancelamento aprovado</span>
        <span class="block text-purple-700/80 mt-1">O valor de R$ {{ number_format($pedido->total_reais, 2, ',', '.') }} foi reembolsado.</span>
    </div>
@endif
