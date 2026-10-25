@extends('layouts.app')
 
@section('slot')
<div class="max-w-4xl mx-auto pt-6 pb-10 px-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
 
        <div class="md:col-span-2 bg-white shadow-xl rounded-3xl p-8 border border-gray-100">
            <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter mb-2 flex items-center gap-2">
                <i class="fas fa-credit-card text-indigo-600"></i> Escolha a Forma de Pagamento
            </h2>
            <p class="text-xs text-gray-400 mb-6">Selecione uma das opções abaixo para prosseguir.</p>
 
            <form id="form-pagamento" action="{{ route('checkout.finalizar') }}" method="POST" class="space-y-6">
                @csrf
 
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Dados do pagador</h3>
 
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nome completo</label>
                        <input type="text" name="nome" id="nome" value="{{ Auth::user()->name }}" required maxlength="150"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
 
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">CPF</label>
                            <input type="text" name="cpf" id="cpf" required maxlength="14" placeholder="000.000.000-00" inputmode="numeric"
                                   class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Celular</label>
                            <input type="text" name="celular" id="celular" required maxlength="15" placeholder="(11) 99999-9999" inputmode="numeric"
                                   class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>
                </div>
 
                <div class="space-y-3">
                    @if(($config['pagamento_pix'] ?? true) == true)
                        <label class="metodo-opcao flex items-center gap-4 p-4 bg-white border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-indigo-500 transition-all">
                            <input type="radio" name="metodo_pagamento" value="pix" required class="text-indigo-600 focus:ring-indigo-500 w-5 h-5 border-gray-300">
                            <div class="text-emerald-500 text-xl bg-emerald-50 w-10 h-10 rounded-xl flex items-center justify-center">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div>
                                <span class="block font-bold text-gray-800 text-sm">Pagar com PIX</span>
                                <span class="block text-xs text-gray-400">QR Code gerado na hora, aprovação imediata</span>
                            </div>
                        </label>
                    @endif
 
                    @if(($config['pagamento_credito'] ?? true) == true)
                        <label class="metodo-opcao flex items-center gap-4 p-4 bg-white border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-indigo-500 transition-all">
                            <input type="radio" name="metodo_pagamento" value="credito" class="text-indigo-600 focus:ring-indigo-500 w-5 h-5 border-gray-300">
                            <div class="text-blue-500 text-xl bg-blue-50 w-10 h-10 rounded-xl flex items-center justify-center">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div>
                                <span class="block font-bold text-gray-800 text-sm">Cartão de Crédito</span>
                                <span class="block text-xs text-gray-400">Visa, Mastercard, Elo e outros</span>
                            </div>
                        </label>
 
                        <div id="secao-cartao" class="hidden border-2 border-indigo-100 bg-indigo-50/40 rounded-2xl p-5">
 
                            <div class="relative mx-auto mb-6 w-full max-w-xs h-44 rounded-2xl p-5 text-white shadow-xl bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-900 overflow-hidden">
                                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full"></div>
                                <div class="flex justify-between items-start">
                                    <div class="w-10 h-7 rounded-md bg-gradient-to-br from-yellow-200 to-yellow-500"></div>
                                    <span id="preview-bandeira" class="text-sm font-black tracking-widest">CARTÃO</span>
                                </div>
                                <div id="preview-numero" class="mt-6 font-mono text-lg tracking-widest">•••• •••• •••• ••••</div>
                                <div class="mt-4 flex justify-between text-[10px] uppercase">
                                    <div>
                                        <span class="block text-white/60">Titular</span>
                                        <span id="preview-nome" class="font-bold text-xs truncate block max-w-[170px]">NOME DO TITULAR</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-white/60">Validade</span>
                                        <span id="preview-validade" class="font-bold text-xs">MM/AA</span>
                                    </div>
                                </div>
                            </div>
 
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Número do cartão</label>
                                    <input type="text" name="cartao_numero" id="cartao_numero" inputmode="numeric" autocomplete="off"
                                           maxlength="23" placeholder="0000 0000 0000 0000"
                                           class="campo-cartao w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nome impresso no cartão</label>
                                    <input type="text" name="cartao_nome" id="cartao_nome" autocomplete="off" maxlength="100" placeholder="Como está no cartão"
                                           class="campo-cartao w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm uppercase focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Validade</label>
                                        <input type="text" name="cartao_validade" id="cartao_validade" inputmode="numeric" autocomplete="off" maxlength="5" placeholder="MM/AA"
                                               class="campo-cartao w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">CVV</label>
                                        <input type="password" name="cartao_cvv" id="cartao_cvv" inputmode="numeric" autocomplete="off" maxlength="4" placeholder="•••"
                                               class="campo-cartao w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Parcelas</label>
                                        <select name="cartao_parcelas" id="cartao_parcelas"
                                                class="w-full bg-white border border-gray-200 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                            @for($p = 1; $p <= 12; $p++)
                                                @if($p === 1 || intdiv($totalCentavos, $p) >= 1000)
                                                    <option value="{{ $p }}">{{ $p }}x de R$ {{ number_format(($totalCentavos / $p) / 100, 2, ',', '.') }}</option>
                                                @endif
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
 
                            <p class="text-[11px] text-gray-400 mt-4">
                                <i class="fas fa-flask mr-1"></i> Ambiente de demonstração: o pagamento com cartão é simulado e nenhum dado é armazenado.
                                Use <b>4242 4242 4242 4242</b> para aprovar ou <b>4000 0000 0000 0002</b> para recusar.
                            </p>
                        </div>
                    @endif
 
                    @if(($config['pagamento_debito'] ?? true) == true)
                        <label class="metodo-opcao flex items-center gap-4 p-4 bg-white border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-indigo-500 transition-all">
                            <input type="radio" name="metodo_pagamento" value="boleto" class="text-indigo-600 focus:ring-indigo-500 w-5 h-5 border-gray-300">
                            <div class="text-orange-500 text-xl bg-orange-50 w-10 h-10 rounded-xl flex items-center justify-center">
                                <i class="fas fa-barcode"></i>
                            </div>
                            <div>
                                <span class="block font-bold text-gray-800 text-sm">Boleto</span>
                                <span class="block text-xs text-gray-400">Vencimento em 3 dias, download em PDF</span>
                            </div>
                        </label>
                    @endif
                </div>
 
                <div id="erro-pagamento" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-4"></div>
 
                <div class="pt-4 flex justify-between items-center">
                    <a href="{{ route('carrinho.index') }}" class="text-xs font-bold uppercase tracking-wider text-gray-400">
                        <i class="fas fa-chevron-left mr-1"></i> Voltar ao carrinho
                    </a>
                    <button type="submit" id="btn-concluir"
                            class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-wait text-white font-bold uppercase text-xs tracking-widest px-8 py-3 rounded-xl shadow-md transition-all cursor-pointer">
                        <span id="btn-texto">Concluir Pedido <i class="fas fa-check ml-2"></i></span>
                    </button>
                </div>
            </form>
        </div>
 
        <div class="bg-gray-50 rounded-3xl p-6 border border-gray-200">
            <h3 class="font-bold text-gray-700 uppercase text-xs tracking-wider mb-4">Resumo do Pedido</h3>
 
            <div class="space-y-4">
                @foreach($carrinho as $item)
                    @php $imagemLimpa = str_replace('storage/', '', $item['imagem']); @endphp
                    <div class="flex gap-3 items-center">
                        <img src="{{ Storage::url($imagemLimpa) }}" class="w-12 h-12 object-cover rounded-xl bg-white shadow-sm flex-shrink-0">
                        <div>
                            <h4 class="font-bold text-xs text-gray-800">{{ $item['nome'] }}</h4>
                            <p class="text-indigo-600 font-black text-xs">
                                {{ $item['quantidade'] }}x R$ {{ number_format($item['preço'], 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
 
            <div class="mt-6 pt-4 border-t border-gray-200 flex justify-between items-end">
                <span class="text-[10px] text-gray-500 uppercase font-bold">Total</span>
                <span class="text-xl font-black text-gray-900">R$ {{ number_format($totalCentavos / 100, 2, ',', '.') }}</span>
            </div>
 
            <div class="mt-6 pt-4 border-t border-gray-200">
                <p class="text-[10px] text-gray-500 uppercase font-bold">Endereço de Entrega</p>
                <p class="text-xs text-gray-700">{{ $endereco['logradouro'] }}, {{ $endereco['numero'] }}</p>
                <p class="text-xs text-gray-700">{{ $endereco['bairro'] }} - {{ $endereco['cidade'] }}</p>
            </div>
        </div>
    </div>
</div>
 
<script>
(function () {
    const form       = document.getElementById('form-pagamento');
    const btn        = document.getElementById('btn-concluir');
    const btnTexto   = document.getElementById('btn-texto');
    const caixaErro  = document.getElementById('erro-pagamento');
    const secaoCart  = document.getElementById('secao-cartao');
 
    document.getElementById('cpf').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 11);
        v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.value = v;
    });
    document.getElementById('celular').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 11);
        v = v.length > 10
            ? v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3')
            : v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        this.value = v.replace(/[-\s()]+$/, '');
    });
 
    document.querySelectorAll('input[name="metodo_pagamento"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.metodo-opcao').forEach(l => l.classList.remove('border-indigo-500', 'bg-indigo-50/30'));
            radio.closest('.metodo-opcao').classList.add('border-indigo-500', 'bg-indigo-50/30');
            esconderErro();
 
            if (!secaoCart) return;
            const ehCartao = radio.value === 'credito';
            secaoCart.classList.toggle('hidden', !ehCartao);
            secaoCart.querySelectorAll('.campo-cartao').forEach(c => c.required = ehCartao);
        });
    });
 
    function bandeira(n) {
        if (/^(4011|4312|4389|4514|4576|5041|5066|5067|509|6277|6362|6363|650|6516|6550)/.test(n)) return 'ELO';
        if (/^(606282|3841)/.test(n)) return 'HIPERCARD';
        if (/^3[47]/.test(n)) return 'AMEX';
        if (/^4/.test(n)) return 'VISA';
        if (/^(5[1-5]|2[2-7])/.test(n)) return 'MASTERCARD';
        return 'CARTÃO';
    }
 
    const campoNumero   = document.getElementById('cartao_numero');
    const campoNome     = document.getElementById('cartao_nome');
    const campoValidade = document.getElementById('cartao_validade');
    const campoCvv      = document.getElementById('cartao_cvv');
 
    campoNumero?.addEventListener('input', function () {
        const n = this.value.replace(/\D/g, '').slice(0, 19);
        this.value = n.replace(/(\d{4})(?=\d)/g, '$1 ');
        document.getElementById('preview-numero').innerText =
            (n + '•'.repeat(Math.max(0, 16 - n.length))).replace(/(.{4})(?=.)/g, '$1 ');
        const b = bandeira(n);
        document.getElementById('preview-bandeira').innerText = b;
        campoCvv.maxLength = b === 'AMEX' ? 4 : 3;
    });
 
    campoNome?.addEventListener('input', function () {
        document.getElementById('preview-nome').innerText = this.value.toUpperCase() || 'NOME DO TITULAR';
    });
 
    campoValidade?.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 4);
        if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
        this.value = v;
        document.getElementById('preview-validade').innerText = v || 'MM/AA';
    });
 
    campoCvv?.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });
 
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        esconderErro();
 
        const metodo = form.querySelector('input[name="metodo_pagamento"]:checked')?.value;
        if (!metodo) return mostrarErro('Escolha uma forma de pagamento.');
 
        carregando(true);
        const formData = new FormData(form);
 
        if (metodo !== 'credito') {
            ['cartao_numero', 'cartao_nome', 'cartao_validade', 'cartao_cvv', 'cartao_parcelas'].forEach(c => formData.delete(c));
        }
 
        try {
            const resposta = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });
            const dados = await resposta.json();
 
            if (resposta.ok && dados.success) {
                window.location.href = dados.redirect_url;
                return;
            }
 
            const errosValidacao = dados.errors ? Object.values(dados.errors).flat().join('<br>') : null;
            mostrarErro(errosValidacao || dados.message || 'Erro desconhecido ao finalizar o pedido.');
        } catch (erro) {
            console.error(erro);
            mostrarErro(erro.message || 'Ocorreu um erro ao processar o pagamento.');
        }
 
        carregando(false);
    });
 
    function carregando(ativo) {
        btn.disabled = ativo;
        btnTexto.innerHTML = ativo
            ? '<i class="fas fa-spinner fa-spin mr-2"></i> Processando...'
            : 'Concluir Pedido <i class="fas fa-check ml-2"></i>';
    }
    function mostrarErro(msg) {
        caixaErro.innerHTML = msg;
        caixaErro.classList.remove('hidden');
    }
    function esconderErro() {
        caixaErro.classList.add('hidden');
    }
})();
</script>
@endsection