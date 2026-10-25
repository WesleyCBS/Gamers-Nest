<section id="historico-compras">
    <header class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                <i class="fas fa-receipt text-indigo-600 mr-1"></i> Histórico de compras
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Suas compras mais recentes.
            </p>
        </div>
        @php $totalPedidos = $totalPedidos ?? $pedidos->count(); @endphp
        @if($totalPedidos)
            <a href="{{ route('pedidos.meus') }}" class="text-xs font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-800 whitespace-nowrap">
                Ver todas ({{ $totalPedidos }}) <i class="fas fa-arrow-right ml-1"></i>
            </a>
        @endif
    </header>

    @forelse($pedidos as $pedido)
        <details class="group border border-gray-100 rounded-2xl mb-3 bg-gray-50/60 overflow-hidden">
            <summary class="flex flex-wrap items-center gap-x-6 gap-y-2 p-4 cursor-pointer list-none hover:bg-gray-50">
                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center">
                    <i class="fas {{ $pedido->metodo_icone }}"></i>
                </div>
                <div class="flex-1 min-w-[140px]">
                    <span class="block text-sm font-bold text-gray-800">{{ $pedido->codigo }}</span>
                    <span class="block text-xs text-gray-400">{{ $pedido->created_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</span>
                </div>
                <div class="text-xs text-gray-500">
                    {{ $pedido->metodo_label }}
                    @if($pedido->metodo === 'credito' && $pedido->parcelas)
                        · {{ $pedido->parcelas }}x
                    @endif
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full {{ $pedido->status_cor }}">
                    {{ $pedido->status_label }}
                </span>
                <span class="text-sm font-black text-indigo-600 min-w-[100px] text-right">
                    R$ {{ number_format($pedido->total_reais, 2, ',', '.') }}
                </span>
                <i class="fas fa-chevron-down text-gray-300 text-xs transition group-open:rotate-180"></i>
            </summary>

            <div class="border-t border-gray-100 bg-white p-4 space-y-3">
                @foreach($pedido->itens as $item)
                    @php $img = $item->imagem ? str_replace('storage/', '', $item->imagem) : null; @endphp
                    <div class="flex items-center gap-3">
                        @if($img)
                            <img src="{{ Storage::url($img) }}" class="w-12 h-12 rounded-xl object-cover shadow-sm" alt="{{ $item->nome }}">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center text-gray-300"><i class="fas fa-gamepad"></i></div>
                        @endif
                        <div class="flex-1">
                            @if($item->produto_id)
                                <a href="{{ route('produtos.show', $item->produto_id) }}" class="text-sm font-bold text-gray-800 hover:text-indigo-600">{{ $item->nome }}</a>
                            @else
                                <span class="text-sm font-bold text-gray-800">{{ $item->nome }}</span>
                            @endif
                            <span class="block text-xs text-gray-400">{{ $item->quantidade }}x R$ {{ number_format($item->preco, 2, ',', '.') }}</span>
                        </div>
                        <span class="text-sm font-bold text-gray-700">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
                    </div>
                @endforeach

                <div class="pt-3 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-gray-500">
                    @if($pedido->endereco)
                        <div>
                            <span class="block font-bold uppercase text-[10px] text-gray-400">Entrega</span>
                            {{ $pedido->endereco['logradouro'] ?? '' }}, {{ $pedido->endereco['numero'] ?? '' }} — {{ $pedido->endereco['bairro'] ?? '' }}, {{ $pedido->endereco['cidade'] ?? '' }}
                        </div>
                    @endif
                    @if($pedido->metodo === 'credito' && $pedido->cartao_final)
                        <div>
                            <span class="block font-bold uppercase text-[10px] text-gray-400">Cartão</span>
                            {{ $pedido->cartao_bandeira }} •••• {{ $pedido->cartao_final }}
                            @if($pedido->parcelas)
                                · {{ $pedido->parcelas }}x de R$ {{ number_format($pedido->total_reais / $pedido->parcelas, 2, ',', '.') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </details>
    @empty
        <div class="text-center py-10 border-2 border-dashed border-gray-200 rounded-2xl">
            <i class="fas fa-shopping-bag text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500 mb-4">Você ainda não fez nenhuma compra.</p>
            <a href="{{ route('produtos.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-widest px-6 py-3 rounded-xl">
                Ver catálogo
            </a>
        </div>
    @endforelse
</section>
