@extends('layouts.app')

@section('slot')
<div class="max-w-6xl mx-auto py-12 px-4">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">
            <i class="fas fa-chart-line mr-2 text-indigo-600"></i> Histórico de Vendas
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
            <span class="block text-[10px] font-bold uppercase tracking-widest text-indigo-200">Faturamento</span>
            <span class="text-2xl font-black">R$ {{ number_format($resumo['faturamento'], 2, ',', '.') }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.pedidos') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar por cliente, e-mail ou nº do pedido"
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
                <div class="col-span-3">Cliente</div>
                <div class="col-span-2">Pagamento</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-2 text-right">Total</div>
            </div>

            @foreach($pedidos as $pedido)
                @php
                    $nomeCliente  = $pedido->user->name ?? ($pedido->cliente['name'] ?? 'Usuário removido');
                    $emailCliente = $pedido->user->email ?? ($pedido->cliente['email'] ?? '');
                @endphp
                <details class="group border-b border-gray-50 last:border-0">
                    <summary class="grid grid-cols-2 md:grid-cols-12 gap-4 px-6 py-4 items-center cursor-pointer list-none hover:bg-gray-50/70">
                        <div class="md:col-span-3">
                            <span class="block text-sm font-bold text-gray-800">{{ $pedido->codigo }}</span>
                            <span class="block text-xs text-gray-400">{{ $pedido->created_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="md:col-span-3 min-w-0">
                            <span class="block text-sm font-bold text-gray-700 truncate">{{ $nomeCliente }}</span>
                            <span class="block text-xs text-gray-400 truncate">{{ $emailCliente }}</span>
                        </div>
                        <div class="md:col-span-2 text-xs text-gray-600">
                            <i class="fas {{ $pedido->metodo_icone }} mr-1"></i> {{ $pedido->metodo_label }}
                            @if($pedido->metodo === 'credito' && $pedido->parcelas)
                                <span class="text-gray-400">· {{ $pedido->parcelas }}x</span>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full {{ $pedido->status_cor }}">
                                {{ $pedido->status_label }}
                            </span>
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
                                        <span class="block text-sm font-bold text-gray-800">{{ $item->nome }}</span>
                                        <span class="block text-xs text-gray-400">{{ $item->quantidade }}x R$ {{ number_format($item->preco, 2, ',', '.') }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 pr-2">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-4 text-xs text-gray-600">
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Cliente</span>
                                {{ $pedido->cliente['name'] ?? $nomeCliente }}<br>
                                @if(!empty($pedido->cliente['taxId']))
                                    CPF {{ preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $pedido->cliente['taxId']) }}<br>
                                @endif
                                {{ $pedido->cliente['cellphone'] ?? '' }}
                            </div>
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
            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Nenhum pedido encontrado.</p>
        </div>
    @endif
</div>
@endsection
