@extends('layouts.app')
 
@section('slot')
@php
    $status   = $cobranca['status'] ?? 'PENDING';
    $pago     = in_array($status, ['PAID', 'APPROVED']);
    $devMode  = $cobranca['devMode'] ?? false;
    $total    = ($cobranca['amount'] ?? ($pedido['total'] ?? 0)) / 100;
    $nomes    = ['pix' => 'PIX', 'credito' => 'Cartão de Crédito', 'boleto' => 'Boleto'];
@endphp
 
<div class="max-w-3xl mx-auto py-12 px-4">
    <div class="bg-white shadow-xl rounded-3xl p-8 border border-gray-100">
 
        <div class="text-center mb-8">
            <div id="icone-status" class="text-6xl mb-4 {{ $pago ? 'text-green-500' : 'text-amber-500' }}">
                <i class="fas {{ $pago ? 'fa-check-circle' : 'fa-clock' }}"></i>
            </div>
            <h2 id="titulo-status" class="text-2xl font-black text-gray-800 uppercase tracking-tighter mb-2">
                {{ $pago ? 'Pagamento Aprovado!' : 'Pedido Gerado — Aguardando Pagamento' }}
            </h2>
            <p class="text-gray-500">
                Pedido <span class="font-bold text-gray-700">{{ $pedido['id'] ?? '' }}</span> ·
                {{ $nomes[$metodo] ?? strtoupper($metodo) }} ·
                <span class="font-bold text-indigo-600">R$ {{ number_format($total, 2, ',', '.') }}</span>
            </p>
            @if($devMode)
                <span class="inline-block mt-3 bg-amber-100 text-amber-800 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full">
                    Modo teste (sandbox)
                </span>
            @endif
        </div>
 
        @if($metodo === 'pix')
            <div id="bloco-pix" class="{{ $pago ? 'hidden' : '' }} bg-emerald-50 border border-emerald-100 rounded-2xl p-6 text-center">
                <h3 class="text-sm font-bold text-emerald-900 uppercase tracking-wider mb-4">Escaneie o QR Code para pagar</h3>
 
                <img src="{{ $cobranca['brCodeBase64'] }}" alt="QR Code PIX" class="w-56 h-56 mx-auto bg-white p-3 rounded-2xl shadow-sm">
 
                <p class="text-xs text-emerald-700 mt-4 mb-2 font-bold uppercase">Ou use o PIX copia e cola</p>
                <div class="flex gap-2">
                    <input id="pix-copia-cola" type="text" readonly value="{{ $cobranca['brCode'] }}"
                           class="flex-1 bg-white border border-emerald-200 rounded-xl px-3 py-2 text-xs font-mono text-gray-600">
                    <button type="button" onclick="copiar('pix-copia-cola', this)"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase px-4 rounded-xl">
                        Copiar
                    </button>
                </div>
 
                @if(!empty($cobranca['expiresAt']))
                    <p class="text-[11px] text-emerald-700 mt-4">
                        Válido até {{ \Carbon\Carbon::parse($cobranca['expiresAt'])->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}
                    </p>
                @endif
                <p class="text-[11px] text-gray-400 mt-2">
                    <i class="fas fa-sync fa-spin mr-1"></i> Esta página atualiza sozinha quando o pagamento for confirmado.
                </p>
            </div>
        @endif
 
        @if($metodo === 'credito')
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-blue-900 uppercase tracking-wider mb-4">Pagamento com cartão</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-blue-500">Cartão</span>
                        <span class="font-bold text-gray-800">{{ $cobranca['brand'] ?? '' }} •••• {{ $cobranca['lastFour'] ?? '' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-blue-500">Status</span>
                        <span id="status-texto" class="font-bold {{ $pago ? 'text-green-600' : 'text-amber-600' }}">{{ $status }}</span>
                    </div>
                    @if(!empty($cobranca['installmentCount']))
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-blue-500">Parcelas</span>
                            <span class="font-bold text-gray-800">{{ $cobranca['installmentCount'] }}x de R$ {{ number_format(($cobranca['amount'] / $cobranca['installmentCount']) / 100, 2, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
 
        @if($metodo === 'boleto')
            <div class="bg-orange-50 border border-orange-100 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-orange-900 uppercase tracking-wider mb-4">Seu boleto foi gerado</h3>
 
                @if(!empty($cobranca['barCode']))
                    <p class="text-xs text-orange-700 font-bold uppercase mb-2">Linha digitável</p>
                    <div class="flex gap-2 mb-4">
                        <input id="linha-digitavel" type="text" readonly value="{{ $cobranca['barCode'] }}"
                               class="flex-1 bg-white border border-orange-200 rounded-xl px-3 py-2 text-xs font-mono text-gray-600">
                        <button type="button" onclick="copiar('linha-digitavel', this)"
                                class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold uppercase px-4 rounded-xl">
                            Copiar
                        </button>
                    </div>
                @endif
 
                @if(!empty($cobranca['expiresAt']))
                    <p class="text-xs text-orange-700 mb-4">
                        Vencimento: <b>{{ \Carbon\Carbon::parse($cobranca['expiresAt'])->timezone('America/Sao_Paulo')->format('d/m/Y') }}</b>
                    </p>
                @endif
 
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('checkout.boleto.pdf') }}"
                       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold uppercase tracking-widest px-6 py-3 rounded-xl shadow-md">
                        <i class="fas fa-file-pdf"></i> Baixar boleto (PDF)
                    </a>
                    @if(!empty($cobranca['url']))
                        <a href="{{ $cobranca['url'] }}" target="_blank"
                           class="inline-flex items-center gap-2 bg-white border border-orange-200 text-orange-700 text-xs font-bold uppercase tracking-widest px-6 py-3 rounded-xl">
                            <i class="fas fa-external-link-alt"></i> Ver no AbacatePay
                        </a>
                    @endif
                </div>
            </div>
        @endif
 
        @if($devMode && in_array($metodo, ['pix', 'boleto']))
            <div id="bloco-simular" class="{{ $pago ? 'hidden' : '' }} mt-6 border-2 border-dashed border-amber-300 bg-amber-50 rounded-2xl p-5 text-center">
                <p class="text-xs text-amber-800 mb-3">
                    Você está no modo teste do AbacatePay. Clique abaixo para simular que o cliente pagou.
                </p>
                <button type="button" id="btn-simular"
                        class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold uppercase tracking-widest px-6 py-3 rounded-xl shadow-md">
                    <i class="fas fa-flask mr-1"></i> Simular pagamento
                </button>
                <p id="simular-erro" class="hidden text-xs text-red-600 mt-3"></p>
            </div>
        @endif
 
        <div class="border-t border-gray-100 pt-6 mt-6 flex justify-between items-center">
            <a href="{{ route('produtos.index') }}" class="text-xs font-bold uppercase tracking-widest text-indigo-600 hover:text-indigo-800 transition">
                <i class="fas fa-arrow-left mr-1"></i> Voltar à Loja
            </a>
        </div>
    </div>
</div>
 
<script>
    function copiar(idCampo, botao) {
        const campo = document.getElementById(idCampo);
        navigator.clipboard.writeText(campo.value).then(() => {
            const original = botao.innerText;
            botao.innerText = 'Copiado!';
            setTimeout(() => botao.innerText = original, 2000);
        });
    }
 
    function marcarComoPago() {
        document.getElementById('icone-status').className = 'text-6xl mb-4 text-green-500';
        document.getElementById('icone-status').innerHTML = '<i class="fas fa-check-circle"></i>';
        document.getElementById('titulo-status').innerText = 'Pagamento Aprovado!';
        document.getElementById('bloco-pix')?.classList.add('hidden');
        document.getElementById('bloco-simular')?.classList.add('hidden');
    }
 
    document.getElementById('btn-simular')?.addEventListener('click', async function () {
        const erro = document.getElementById('simular-erro');
        erro.classList.add('hidden');
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Simulando...';
 
        try {
            const resp = await fetch('{{ route('checkout.simular') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            });
            const dados = await resp.json();
            if (resp.ok && dados.success) {
                marcarComoPago();
                return;
            }
            erro.innerText = dados.message || 'Não foi possível simular.';
        } catch (e) {
            erro.innerText = 'Erro de rede ao simular o pagamento.';
        }
        erro.classList.remove('hidden');
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-flask mr-1"></i> Simular pagamento';
    });
 
    @if($metodo === 'pix' && !$pago)
        const intervalo = setInterval(async () => {
            try {
                const resp = await fetch('{{ route('checkout.status') }}', { headers: { 'Accept': 'application/json' } });
                const dados = await resp.json();
                if (['PAID', 'APPROVED'].includes(dados.status)) {
                    clearInterval(intervalo);
                    marcarComoPago();
                } else if (['EXPIRED', 'CANCELLED'].includes(dados.status)) {
                    clearInterval(intervalo);
                    document.getElementById('titulo-status').innerText = 'PIX expirado ou cancelado';
                }
            } catch (e) { }
        }, 5000);
    @endif
</script>
@endsection