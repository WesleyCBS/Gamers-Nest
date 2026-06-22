@extends('layouts.app')

@section('slot')
<div class="max-w-4xl mx-auto py-10 px-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="md:col-span-2 bg-white shadow-xl rounded-3xl p-8 border border-gray-100">
            <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter mb-6 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-indigo-600"></i> Endereço de Entrega
            </h2>

            <form action="{{ route('checkout.salvar_endereco') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">CEP</label>
                        <input type="text" name="cep" id="cep" placeholder="00000-000" required class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Rua / Logradouro</label>
                        <input type="text" name="rua" id="rua" placeholder="Av. Principal" required class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Número</label>
                        <input type="text" name="numero" placeholder="123" required class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Bairro</label>
                        <input type="text" name="bairro" id="bairro" placeholder="Centro" required class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Cidade</label>
                    <input type="text" name="cidade" id="cidade" placeholder="Sua Cidade" required class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold uppercase text-xs tracking-widest px-8 py-3 rounded-xl shadow-md transition-all">
                        Ir para o Pagamento <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-gray-50 rounded-3xl p-6 border border-gray-200 h-fit">
            <h3 class="font-bold text-gray-700 uppercase text-xs tracking-wider mb-4">Resumo do Pedido</h3>
            
            @if(isset($roupa) && $roupa)
                <div class="flex gap-3 items-center">
                    <img src="{{ Storage::url($roupa->imagem) }}" class="w-16 h-16 object-cover rounded-xl bg-white shadow-sm">
                    <div>
                        <h4 class="font-bold text-sm text-gray-800 leading-tight">{{ $roupa->nome }}</h4>
                        <span class="text-xs text-gray-400">1 unidade</span>
                        <p class="text-indigo-600 font-black text-sm mt-1">R$ {{ number_format($roupa->preço, 2, ',', '.') }}</p>
                    </div>
                </div>
            @elseif(session()->has('carrinho'))
                <div class="space-y-3">
                    @foreach(session('carrinho') as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ $item['quantidade'] }}x {{ $item['nome'] }}</span>
                            <span class="font-bold text-gray-800">R$ {{ number_format($item['preço'] * $item['quantidade'], 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="border-t border-gray-200 pt-3 mt-3 flex justify-between">
                        <span class="font-bold text-gray-800">Total</span>
                        <span class="font-black text-indigo-600">
                            R$ {{ number_format(collect(session('carrinho'))->sum(fn($i) => $i['preço'] * $i['quantidade']), 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            @else
                <p class="text-gray-500 text-sm">Nenhum item selecionado.</p>
            @endif
        </div>
    </div>
</div>

<script>
    document.getElementById('cep').addEventListener('blur', function() {
        let cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('rua').value = data.logradouro;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.localidade;
                    } else {
                        alert('CEP não encontrado.');
                    }
                })
                .catch(error => console.error('Erro ao buscar CEP:', error));
        }
    });
</script>
@endsection