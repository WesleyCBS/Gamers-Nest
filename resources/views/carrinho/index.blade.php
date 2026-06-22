@extends('layouts.app')

@section('slot')
<div class="max-w-5xl mx-auto py-12 px-6">
    <div class="flex items-center justify-between mb-10">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">
            <i class="fas fa-shopping-cart mr-2 text-indigo-600"></i> Meu Carrinho
        </h1>
        <a href="{{ route('roupas.index') }}" class="text-indigo-600 font-bold hover:underline">
            <i class="fas fa-arrow-left mr-1"></i> Continuar Comprando
        </a>
    </div>

    @if(session('carrinho') && count(session('carrinho')) > 0)
        <div class="bg-white shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-600 uppercase text-sm">Produto</th>
                        <th class="px-6 py-4 font-bold text-gray-600 uppercase text-sm">Preço</th>
                        <th class="px-6 py-4 font-bold text-gray-600 uppercase text-sm text-center">Qtd</th>
                        <th class="px-6 py-4 font-bold text-gray-600 uppercase text-sm">Subtotal</th>
                        <th class="px-6 py-4 font-bold text-gray-600 uppercase text-sm text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $total = 0 @endphp
                    @foreach(session('carrinho') as $id => $detalhes)
                        @php 
                            $subtotal = $detalhes['preço'] * $detalhes['quantidade'];
                            $total += $subtotal;
                            $imagemLimpa = str_replace('storage/', '', $detalhes['imagem']);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-6 flex items-center gap-4">
                                <img src="{{ Storage::url($imagemLimpa) }}" class="w-16 h-16 rounded-xl object-cover shadow-sm" alt="{{ $detalhes['nome'] }}">
                                <span class="font-bold text-gray-800 text-lg">{{ $detalhes['nome'] }}</span>
                            </td>
                            <td class="px-6 py-6 text-gray-600 font-medium">
                                R$ {{ number_format($detalhes['preço'], 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-6 text-center font-bold text-gray-700">
                                {{ $detalhes['quantidade'] }}
                            </td>
                            <td class="px-6 py-6 text-indigo-600 font-black text-lg">
                                R$ {{ number_format($subtotal, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-6 text-center">
                                <form action="{{ route('carrinho.remover', $id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-50 text-red-500 w-10 h-10 rounded-full hover:bg-red-500 hover:text-white transition-all" title="Remover item">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-8 bg-gray-50 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <span class="text-gray-500 block uppercase text-xs font-bold tracking-widest">Valor Total</span>
                    <span class="text-4xl font-black text-gray-900 tracking-tighter">
                        R$ {{ number_format($total, 2, ',', '.') }}
                    </span>
                </div>
                <a href="{{ route('checkout.index') }}" 
                   class="w-full md:w-auto bg-green-500 text-white px-12 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-green-600 hover:scale-105 transition-all shadow-lg shadow-green-200 text-center">
                    Finalizar Pedido <i class="fas fa-check ml-2"></i>
                </a>
            </div>
        </div>
    @else
        <div class="bg-white rounded-3xl p-20 text-center shadow-sm border border-dashed border-gray-200">
            <div class="bg-gray-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-shopping-basket text-4xl text-gray-300"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Seu carrinho está vazio</h2>
            <p class="text-gray-500 mb-8">Parece que você ainda não escolheu seus itens.</p>
            <a href="{{ route('roupas.index') }}" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 transition-all">
                Ver Catálogo
            </a>
        </div>
    @endif
</div>
@endsection