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

    @php
        $cartoes = [
            'confirmar'     => ['titulo' => 'A confirmar',          'icone' => 'fa-clipboard-check', 'cor' => 'amber'],
            'concluidas'    => ['titulo' => 'Concluídas',           'icone' => 'fa-check-circle',    'cor' => 'green'],
            'cancelamentos' => ['titulo' => 'Cancelamento solicitado', 'icone' => 'fa-undo',         'cor' => 'red'],
            'cancelados'    => ['titulo' => 'Cancelados',           'icone' => 'fa-ban',             'cor' => 'purple'],
            'pagamento'     => ['titulo' => 'Aguardando pagamento', 'icone' => 'fa-hourglass-half',  'cor' => 'gray'],
        ];
        $estilos = [
            'amber' => ['ativo' => 'border-amber-400 ring-2 ring-amber-200', 'icone' => 'bg-amber-100 text-amber-600', 'numero' => 'text-amber-600'],
            'green' => ['ativo' => 'border-green-500 ring-2 ring-green-200', 'icone' => 'bg-green-100 text-green-600', 'numero' => 'text-green-600'],
            'red'   => ['ativo' => 'border-red-400 ring-2 ring-red-200',     'icone' => 'bg-red-100 text-red-500',     'numero' => 'text-red-500'],
            'gray'  => ['ativo' => 'border-gray-400 ring-2 ring-gray-200',   'icone' => 'bg-gray-100 text-gray-500',   'numero' => 'text-gray-600'],
            'purple' => ['ativo' => 'border-purple-400 ring-2 ring-purple-200', 'icone' => 'bg-purple-100 text-purple-600', 'numero' => 'text-purple-600'],
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-4">
        @foreach($cartoes as $chave => $cartao)
            @php $estilo = $estilos[$cartao['cor']]; @endphp
            <a href="{{ route('admin.pedidos', ['aba' => $chave]) }}"
               class="relative bg-white rounded-2xl p-5 shadow-sm border transition hover:shadow-md {{ $aba === $chave ? $estilo['ativo'] : 'border-gray-100' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">{{ $cartao['titulo'] }}</span>
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs {{ $estilo['icone'] }}">
                        <i class="fas {{ $cartao['icone'] }}"></i>
                    </span>
                </div>
                <span class="text-2xl font-black {{ $estilo['numero'] }}">{{ $contagens[$chave] }}</span>
                @if(in_array($chave, ['confirmar', 'cancelamentos']) && $contagens[$chave] > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-3.5 h-3.5 rounded-full bg-red-500 border-2 border-white"></span>
                @endif
            </a>
        @endforeach
        <div class="col-span-2 md:col-span-1 bg-indigo-600 rounded-2xl p-5 shadow-sm text-white">
            <span class="block text-[10px] font-bold uppercase tracking-widest text-indigo-200 mb-2">Faturamento</span>
            <span class="text-2xl font-black">R$ {{ number_format($faturamento, 2, ',', '.') }}</span>
        </div>
    </div>

    <div class="flex justify-end mb-6">
        <a href="{{ route('admin.pedidos', ['aba' => 'todas']) }}"
           class="text-xs font-bold uppercase tracking-wider {{ $aba === 'todas' ? 'text-indigo-700 underline' : 'text-indigo-500 hover:text-indigo-700' }}">
            Ver todas as vendas ({{ $contagens['todas'] }})
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-4 mb-6">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('admin.pedidos') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="hidden" name="aba" value="{{ $aba }}">
        <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar por cliente, e-mail ou nº do pedido"
               class="md:col-span-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
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
                        <div class="md:col-span-2 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full {{ $pedido->status_cor }}">
                                {{ $pedido->status_label }}
                            </span>
                            @if($pedido->pode_confirmar)
                                <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-amber-100 text-amber-700">
                                    A confirmar
                                </span>
                            @elseif($pedido->confirmado_em && in_array($pedido->status, \App\Models\Pedido::STATUS_PAGOS))
                                <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-emerald-600 text-white">
                                    Concluída
                                </span>
                            @endif
                            @if($pedido->cancelamento === 'solicitado')
                                <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-amber-100 text-amber-700 animate-pulse">
                                    Cancelamento solicitado
                                </span>
                            @elseif($pedido->cancelamento === 'recusado')
                                <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-gray-100 text-gray-500">
                                    Cancel. recusado
                                </span>
                            @endif
                            @if($pedido->cancelado)
                                <span class="block w-full text-[10px] text-gray-400">
                                    por {{ $pedido->cancelado_por_label }}{{ $pedido->cancelado_por_nome ? ' · ' . $pedido->cancelado_por_nome : '' }}
                                </span>
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
                                        <span class="block text-sm font-bold text-gray-800">{{ $item->nome }}</span>
                                        <span class="block text-xs text-gray-400">{{ $item->quantidade }}x R$ {{ number_format($item->preco, 2, ',', '.') }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 pr-2">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-4 text-xs text-gray-600">
                            @if($pedido->pode_confirmar)
                                <form action="{{ route('admin.pedidos.confirmar', $pedido->id) }}" method="POST">
                                    @csrf
                                    <button type="button" onclick="confirmarVenda(this.form, '{{ $pedido->codigo }}')"
                                            class="flex items-center justify-center gap-2 w-full bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold uppercase tracking-wider py-2.5 rounded-xl shadow-md transition">
                                        <i class="fas fa-clipboard-check"></i> Confirmar venda
                                    </button>
                                </form>
                            @elseif($pedido->confirmado_em && !$pedido->cancelado)
                                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                                    <span class="block text-[10px] font-bold uppercase tracking-widest text-emerald-600">
                                        <i class="fas fa-check-circle mr-1"></i> Venda confirmada
                                    </span>
                                    <span class="block text-emerald-700/80 mt-1">em {{ $pedido->confirmado_em->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                            @if($pedido->pode_cancelar_admin)
                                <form action="{{ route('admin.pedidos.cancelar', $pedido->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="motivo" value="">
                                    <button type="button" onclick="cancelarVendaAdmin(this.form, '{{ $pedido->codigo }}')"
                                            class="flex items-center justify-center gap-2 w-full border-2 border-red-200 text-red-500 hover:bg-red-500 hover:border-red-500 hover:text-white text-[11px] font-bold uppercase tracking-wider py-2 rounded-xl transition">
                                        <i class="fas fa-ban"></i> Cancelar venda
                                    </button>
                                </form>
                            @endif
                            @if($pedido->cancelamento === 'solicitado')
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 space-y-2">
                                    <span class="block text-[10px] font-bold uppercase tracking-widest text-amber-600">
                                        <i class="fas fa-undo mr-1"></i> Cancelamento solicitado
                                    </span>
                                    <p class="text-gray-600 italic">"{{ $pedido->cancelamento_motivo ?: 'O cliente não informou o motivo.' }}"</p>
                                    @if($pedido->cancelamento_solicitado_em)
                                        <span class="block text-[10px] text-gray-400">em {{ $pedido->cancelamento_solicitado_em->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</span>
                                    @endif
                                    <div class="flex gap-2 pt-1">
                                        <form action="{{ route('admin.pedidos.cancelamento', $pedido->id) }}" method="POST" class="flex-1">
                                            @csrf
                                            <input type="hidden" name="acao" value="aprovar">
                                            <button type="button" onclick="responderCancelamento(this.form, 'aprovar', '{{ $pedido->codigo }}')"
                                                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold uppercase tracking-wider py-2 rounded-lg transition">
                                                <i class="fas fa-check mr-1"></i> Aprovar
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.pedidos.cancelamento', $pedido->id) }}" method="POST" class="flex-1">
                                            @csrf
                                            <input type="hidden" name="acao" value="recusar">
                                            <button type="button" onclick="responderCancelamento(this.form, 'recusar', '{{ $pedido->codigo }}')"
                                                    class="w-full bg-white border border-red-200 text-red-500 hover:bg-red-500 hover:text-white text-[11px] font-bold uppercase tracking-wider py-2 rounded-lg transition">
                                                <i class="fas fa-times mr-1"></i> Recusar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @elseif($pedido->cancelado)
                                <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 space-y-1">
                                    <span class="block text-[10px] font-bold uppercase tracking-widest text-purple-600">
                                        <i class="fas fa-ban mr-1"></i>
                                        {{ $pedido->status === 'REFUNDED' ? 'Cancelado e reembolsado' : ($pedido->status === 'EXPIRED' ? 'Pagamento expirado' : 'Cancelado') }}
                                    </span>
                                    <span class="block text-gray-600">
                                        Cancelado por: <b>{{ $pedido->cancelado_por_label }}</b>
                                        @if($pedido->cancelado_por_nome)
                                            · {{ $pedido->cancelado_por_nome }}
                                        @endif
                                    </span>
                                    @if($pedido->cancelamento === 'aprovado')
                                        <span class="block text-gray-500">Solicitado pelo cliente e aprovado pela loja.</span>
                                    @endif
                                    @if($pedido->cancelado_em)
                                        <span class="block text-[10px] text-gray-400">em {{ $pedido->cancelado_em->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</span>
                                    @endif
                                    @if($pedido->cancelamento_motivo)
                                        <span class="block text-gray-500 italic pt-1">Motivo: "{{ $pedido->cancelamento_motivo }}"</span>
                                    @endif
                                </div>
                            @elseif($pedido->cancelamento === 'recusado')
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                                    <span class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Cancelamento recusado</span>
                                    @if($pedido->cancelamento_motivo)
                                        <span class="block text-gray-500 italic mt-1">"{{ $pedido->cancelamento_motivo }}"</span>
                                    @endif
                                </div>
                            @endif
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
                                <a href="{{ route('admin.pedidos.etiqueta', $pedido->id) }}" target="_blank"
                                   class="flex items-center justify-center gap-2 w-full bg-gray-900 hover:bg-gray-700 text-white text-[11px] font-bold uppercase tracking-wider py-2.5 rounded-xl transition">
                                    <i class="fas fa-print"></i> Imprimir etiqueta
                                </a>
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
            <p class="text-gray-500">
                @switch($aba)
                    @case('confirmar') Nenhuma venda aguardando sua confirmação. @break
                    @case('concluidas') Nenhuma venda concluída ainda. @break
                    @case('cancelamentos') Nenhum cancelamento para analisar. @break
                    @case('cancelados') Nenhum pedido cancelado. @break
                    @case('pagamento') Nenhum pedido aguardando pagamento. @break
                    @default Nenhum pedido encontrado.
                @endswitch
            </p>
        </div>
    @endif
</div>

<script>
    (function () {
        try {
            const posicao = sessionStorage.getItem('scroll-vendas');
            if (posicao !== null) {
                sessionStorage.removeItem('scroll-vendas');
                window.scrollTo(0, parseInt(posicao, 10));
            }
        } catch (e) {}
    })();

    function guardarPosicao() {
        try { sessionStorage.setItem('scroll-vendas', window.scrollY); } catch (e) {}
    }

    function cancelarVendaAdmin(form, codigo) {
        Swal.fire({
            title: 'Cancelar venda?',
            text: 'O pedido ' + codigo + ' será cancelado, o valor reembolsado ao cliente e os produtos voltam ao estoque.',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Escreva o motivo do cancelamento (o cliente vai ver)',
            inputAttributes: { maxlength: 500 },
            inputValidator: (valor) => {
                if (!valor || valor.trim().length < 3) {
                    return 'Informe o motivo do cancelamento.';
                }
            },
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Cancelar venda',
            cancelButtonText: 'Voltar',
        }).then((resultado) => {
            if (resultado.isConfirmed) {
                form.querySelector('input[name="motivo"]').value = resultado.value.trim();
                guardarPosicao();
                form.submit();
            }
        });
    }

    function confirmarVenda(form, codigo) {
        Swal.fire({
            title: 'Confirmar venda?',
            text: 'O pedido ' + codigo + ' será marcado como concluído (ex.: separado/enviado ao cliente).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sim, confirmar',
            cancelButtonText: 'Voltar',
        }).then((resultado) => {
            if (resultado.isConfirmed) {
                guardarPosicao();
                form.submit();
            }
        });
    }

    function responderCancelamento(form, acao, codigo) {
        const aprovar = acao === 'aprovar';
        Swal.fire({
            title: aprovar ? 'Aprovar cancelamento?' : 'Recusar cancelamento?',
            text: aprovar
                ? 'O pedido ' + codigo + ' será marcado como reembolsado e os produtos voltam ao estoque.'
                : 'O pedido ' + codigo + ' continua pago e o cliente verá que o cancelamento foi recusado.',
            icon: aprovar ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: aprovar ? '#059669' : '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: aprovar ? 'Sim, aprovar' : 'Sim, recusar',
            cancelButtonText: 'Voltar',
        }).then((resultado) => {
            if (resultado.isConfirmed) {
                guardarPosicao();
                form.submit();
            }
        });
    }
</script>
@endsection
