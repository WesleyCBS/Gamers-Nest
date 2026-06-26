@extends('layouts.app')

@section('slot')
<div class="max-w-4xl mx-auto pt-6 pb-10 px-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
        
        <div class="md:col-span-2 bg-white shadow-xl rounded-3xl p-8 border border-gray-100">
            <div id="container-form">
                <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter mb-2 flex items-center gap-2">
                    <i class="fas fa-credit-card text-indigo-600"></i> Escolha a Forma de Pagamento
                </h2>
                <p class="text-xs text-gray-400 mb-6">Selecione uma das opções abaixo para prosseguir.</p>

                <form id="form-pagamento" action="{{ route('checkout.finalizar') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="space-y-3">
                        @if(($config['pagamento_pix'] ?? true) == true)
                            <label class="flex items-center gap-4 p-4 bg-white border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-indigo-500 transition-all">
                                <input type="radio" name="metodo_pagamento" value="pix" required class="text-indigo-600 focus:ring-indigo-500 w-5 h-5 border-gray-300">
                                <div class="text-emerald-500 text-xl bg-emerald-50 w-10 h-10 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <div><span class="block font-bold text-gray-800 text-sm">Pagar com PIX</span></div>
                            </label>
                        @endif

                        @if(($config['pagamento_credito'] ?? true) == true)
                            <label class="flex items-center gap-4 p-4 bg-white border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-indigo-500 transition-all">
                                <input type="radio" name="metodo_pagamento" value="credito" class="text-indigo-600 focus:ring-indigo-500 w-5 h-5 border-gray-300">
                                <div class="text-blue-500 text-xl bg-blue-50 w-10 h-10 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div><span class="block font-bold text-gray-800 text-sm">Cartão de Crédito</span></div>
                            </label>
                        @endif

                        @if(($config['pagamento_debito'] ?? true) == true)
                            <label class="flex items-center gap-4 p-4 bg-white border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-indigo-500 transition-all">
                                <input type="radio" name="metodo_pagamento" value="debito" class="text-indigo-600 focus:ring-indigo-500 w-5 h-5 border-gray-300">
                                <div class="text-orange-500 text-xl bg-orange-50 w-10 h-10 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-money-check"></i>
                                </div>
                                <div><span class="block font-bold text-gray-800 text-sm">Boleto</span></div>
                            </label>
                        @endif
                    </div>

                    <div class="pt-4 flex justify-between items-center">
                        <a href="{{ route('checkout.endereco') }}" class="text-xs font-bold uppercase tracking-wider text-gray-400">
                            <i class="fas fa-chevron-left mr-1"></i> Voltar endereço
                        </a>
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold uppercase text-xs tracking-widest px-8 py-3 rounded-xl shadow-md transition-all">
                            Concluir Pedido <i class="fas fa-check ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div id="mensagem-sucesso" class="hidden text-center py-10">
                <div class="text-green-500 text-6xl mb-4"><i class="fas fa-check-circle"></i></div>
                <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tighter">Compra Concluída!</h2>
                <p class="text-gray-500 mb-6">Agradecemos pela preferência.</p>
                <a href="{{ route('roupas.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold uppercase text-xs tracking-widest px-8 py-4 rounded-xl transition-all shadow-md">
                    Voltar à Tela Inicial
                </a>
            </div>
        </div>

        <div class="bg-gray-50 rounded-3xl p-6 border border-gray-200">
            <h3 class="font-bold text-gray-700 uppercase text-xs tracking-wider mb-4">Resumo do Pedido</h3>
            
            <div class="space-y-4">
                @foreach($carrinho as $item)
                    <div class="flex gap-3 items-center">
                        <img src="{{ asset('storage/' . $item['imagem']) }}" class="w-12 h-12 object-cover rounded-xl bg-white shadow-sm flex-shrink-0">
                        <div>
                            <h4 class="font-bold text-xs text-gray-800">{{ $item['nome'] }}</h4>
                            <p class="text-indigo-600 font-black text-xs">
                                {{ $item['quantidade'] }}x R$ {{ number_format($item['preço'], 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200">
                <p class="text-[10px] text-gray-500 uppercase font-bold">Endereço de Entrega</p>
                <p class="text-xs text-gray-700">{{ $endereco['rua'] }}, {{ $endereco['numero'] }}</p>
                <p class="text-xs text-gray-700">{{ $endereco['bairro'] }} - {{ $endereco['cidade'] }}</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('form-pagamento').addEventListener('submit', function(e) {
        e.preventDefault(); 
        let form = this;
        let formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(response => {
            if (response.ok) {
                document.getElementById('container-form').classList.add('hidden');
                document.getElementById('mensagem-sucesso').classList.remove('hidden');
            }
        });
    });
</script>
@endsection