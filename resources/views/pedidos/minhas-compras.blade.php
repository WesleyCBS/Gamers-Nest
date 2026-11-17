@extends('layouts.app')

@section('slot')
<div class="max-w-6xl mx-auto py-12 px-4">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">
            <i class="fas fa-receipt mr-2 text-indigo-600"></i> Minhas Compras
        </h1>
        <a href="{{ route('produtos.index') }}" class="text-indigo-600 font-bold hover:underline text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Voltar à loja
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400">Pedidos</span>
            <span class="text-2xl font-black text-gray-800">{{ $resumo['pedidos'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400">Pagos</span>
            <span class="text-2xl font-black text-green-600">{{ $resumo['pagos'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400">Aguardando</span>
            <span class="text-2xl font-black text-amber-500">{{ $resumo['pendentes'] }}</span>
        </div>
        <div class="bg-indigo-600 rounded-2xl p-5 shadow-sm text-white">
            <span class="block text-[10px] font-bold uppercase tracking-widest text-indigo-200">Total gasto</span>
            <span class="text-2xl font-black">R$ {{ number_format($resumo['gasto'], 2, ',', '.') }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-4 mb-6">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('pedidos.meus') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar por produto ou nº do pedido"
               class="md:col-span-2 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        <select name="status" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <option value="">Todos os status</option>
            <option value="pago" @selected(request('status') === 'pago')>Pago</option>
            <option value="pendente" @selected(request('status') === 'pendente')>Aguardando pagamento</option>
        </select>
        <div class="flex gap-2">
            <select name="metodo" class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">Todos os métodos</option>
                <option value="pix" @selected(request('metodo') === 'pix')>PIX</option>
                <option value="credito" @selected(request('metodo') === 'credito')>Cartão</option>
                <option value="boleto" @selected(request('metodo') === 'boleto')>Boleto</option>
            </select>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 rounded-xl" title="Filtrar">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    @if($pedidos->count())
        <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-gray-100">
            <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-3 bg-gray-50 border-b border-gray-100 text-[10px] font-bold uppercase tracking-widest text-gray-500">
                <div class="col-span-3">Pedido</div>
                <div class="col-span-3">Produtos</div>
                <div class="col-span-2">Pagamento</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-2 text-right">Total</div>
            </div>

            @foreach($pedidos as $pedido)
                @php
                    $primeiro = $pedido->itens->first();
                    $outros   = $pedido->itens->count() - 1;
                    $qtdTotal = $pedido->itens->sum('quantidade');
                @endphp
                <details class="group border-b border-gray-50 last:border-0">
                    <summary class="grid grid-cols-2 md:grid-cols-12 gap-4 px-6 py-4 items-center cursor-pointer list-none hover:bg-gray-50/70">
                        <div class="md:col-span-3">
                            <span class="block text-sm font-bold text-gray-800">{{ $pedido->codigo }}</span>
                            <span class="block text-xs text-gray-400">{{ $pedido->created_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="md:col-span-3 min-w-0">
                            <span class="block text-sm font-bold text-gray-700 truncate">{{ $primeiro->nome ?? '—' }}</span>
                            <span class="block text-xs text-gray-400">
                                {{ $qtdTotal }} {{ $qtdTotal === 1 ? 'item' : 'itens' }}
                                @if($outros > 0)
                                    · + {{ $outros }} {{ $outros === 1 ? 'produto' : 'produtos' }}
                                @endif
                            </span>
                        </div>
                        <div class="md:col-span-2 text-xs text-gray-600">
                            <i class="fas {{ $pedido->metodo_icone }} mr-1"></i> {{ $pedido->metodo_label }}
                            @if($pedido->metodo === 'credito' && $pedido->parcelas)
                                <span class="text-gray-400">· {{ $pedido->parcelas }}x</span>
                            @endif
                        </div>
                        <div class="md:col-span-2 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full {{ $pedido->status_cor }}">
                                {{ $pedido->status_label }}
                            </span>
                            @if($pedido->cancelamento === 'solicitado')
                                <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-amber-100 text-amber-700">
                                    Cancelamento solicitado
                                </span>
                            @endif
                            @if($pedido->pode_pagar)
                                <a href="{{ route('pedidos.pagar', $pedido->id) }}" onclick="event.stopPropagation()"
                                   class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                    Pagar
                                </a>
                            @endif
                        </div>
                        <div class="col-span-2 md:col-span-2 text-right">
                            <span class="text-sm font-black text-indigo-600">R$ {{ number_format($pedido->total_reais, 2, ',', '.') }}</span>
                            <i class="fas fa-chevron-down text-gray-300 text-xs ml-2 transition group-open:rotate-180"></i>
                        </div>
                    </summary>

                    <div class="px-6 pb-6 pt-2 bg-gray-50/50 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 space-y-3">
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400">Itens</span>
                            @foreach($pedido->itens as $item)
                                @php $img = $item->imagem ? str_replace('storage/', '', $item->imagem) : null; @endphp
                                <div class="flex items-center gap-3 bg-white rounded-xl p-2 border border-gray-100">
                                    @if($img)
                                        <img src="{{ Storage::url($img) }}" class="w-10 h-10 rounded-lg object-cover" alt="{{ $item->nome }}">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-300"><i class="fas fa-gamepad"></i></div>
                                    @endif
                                    <div class="flex-1">
                                        @if($item->produto_id)
                                            <a href="{{ route('produtos.show', $item->produto_id) }}" class="block text-sm font-bold text-gray-800 hover:text-indigo-600">{{ $item->nome }}</a>
                                        @else
                                            <span class="block text-sm font-bold text-gray-800">{{ $item->nome }}</span>
                                        @endif
                                        <span class="block text-xs text-gray-400">{{ $item->quantidade }}x R$ {{ number_format($item->preco, 2, ',', '.') }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 pr-2">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-4 text-xs text-gray-600">
                            @if($pedido->pode_pagar)
                                <a href="{{ route('pedidos.pagar', $pedido->id) }}"
                                   class="flex items-center justify-center gap-2 w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-widest py-3 rounded-xl shadow-md transition">
                                    <i class="fas {{ $pedido->metodo === 'pix' ? 'fa-qrcode' : 'fa-barcode' }}"></i>
                                    {{ $pedido->metodo === 'pix' ? 'Pagar com PIX' : 'Ver boleto' }}
                                </a>
                            @endif
                            @if($pedido->pode_cancelar)
                                <form action="{{ route('pedidos.cancelar', $pedido->id) }}" method="POST">
                                    @csrf
                                    <button type="button" onclick="cancelarPedido(this.form, '{{ $pedido->codigo }}')"
                                            class="flex items-center justify-center gap-2 w-full border-2 border-red-200 text-red-500 hover:bg-red-500 hover:border-red-500 hover:text-white text-xs font-bold uppercase tracking-widest py-2.5 rounded-xl transition">
                                        <i class="fas fa-times"></i> Cancelar pedido
                                    </button>
                                </form>
                            @endif
                            @include('pedidos.partials.cancelamento-comprador')
                            @if($pedido->endereco)
                                <div>
                                    <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Entrega</span>
                                    {{ $pedido->endereco['logradouro'] ?? '' }}, {{ $pedido->endereco['numero'] ?? '' }}<br>
                                    {{ $pedido->endereco['bairro'] ?? '' }} — {{ $pedido->endereco['cidade'] ?? '' }}<br>
                                    CEP {{ $pedido->endereco['cep'] ?? '' }}
                                </div>
                            @endif
                            @if($pedido->metodo === 'credito' && $pedido->cartao_final)
                                <div>
                                    <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Cartão</span>
                                    {{ $pedido->cartao_bandeira }} •••• {{ $pedido->cartao_final }}
                                    @if($pedido->parcelas)
                                        <br>{{ $pedido->parcelas }}x de R$ {{ number_format($pedido->total_reais / $pedido->parcelas, 2, ',', '.') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </details>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $pedidos->links() }}
        </div>
    @else
        <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-dashed border-gray-200">
            <i class="fas fa-shopping-bag text-4xl text-gray-300 mb-4"></i>
            @if(request()->filled('busca') || request()->filled('status') || request()->filled('metodo'))
                <p class="text-gray-500 mb-4">Nenhuma compra encontrada com esses filtros.</p>
                <a href="{{ route('pedidos.meus') }}" class="text-indigo-600 font-bold text-sm hover:underline">Limpar filtros</a>
            @else
                <p class="text-gray-500 mb-6">Você ainda não fez nenhuma compra.</p>
                <a href="{{ route('produtos.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-widest px-6 py-3 rounded-xl">
                    Ver catálogo
                </a>
            @endif
        </div>
    @endif
</div>

<script>
    function cancelarPedido(form, codigo) {
        Swal.fire({
            title: 'Cancelar pedido?',
            text: 'O pedido ' + codigo + ' será cancelado e não poderá mais ser pago.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sim, cancelar',
            cancelButtonText: 'Voltar',
        }).then((resultado) => {
            if (resultado.isConfirmed) {
                form.submit();
            }
        });
    }
    function solicitarCancelamento(form, codigo) {
        Swal.fire({
            title: 'Solicitar cancelamento?',
            text: 'O pedido ' + codigo + ' já foi pago. A loja vai analisar e, se aprovar, o valor será reembolsado.',
            icon: 'question',
            input: 'textarea',
            inputPlaceholder: 'Conte o motivo do cancelamento (opcional)',
            inputAttributes: { maxlength: 500 },
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Enviar solicitação',
            cancelButtonText: 'Voltar',
        }).then((resultado) => {
            if (resultado.isConfirmed) {
                form.querySelector('input[name="motivo"]').value = resultado.value || '';
                form.submit();
            }
        });
    }
</script>
@endsection
