@extends('layouts.app')

@section('slot')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <a href="{{ route('roupas.index') }}" class="inline-flex items-center text-gray-500 hover:text-indigo-600 mb-8 font-bold transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Voltar ao Catálogo
        </a>

        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row border border-gray-100">
            
            <div class="md:w-1/2 p-10 bg-white flex items-center justify-center">
                <img src="{{ Storage::url(str_replace('storage/', '', $roupa->imagem)) }}" 
                     alt="{{ $roupa->nome }}" 
                     class="max-h-[450px] w-full object-contain hover:scale-105 transition-transform duration-500">
            </div>

            <div class="md:w-1/2 p-12 bg-gray-50 flex flex-col">
                <div class="mb-6">
                    <span class="px-4 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-black uppercase tracking-widest">
                        {{ $roupa->categoria->nome ?? 'Produto' }}
                    </span>
                    <h1 class="text-4xl font-black text-gray-800 mt-4 leading-tight uppercase tracking-tighter">
                        {{ $roupa->nome }}
                    </h1>
                </div>

                <div class="mb-8">
                    <span class="text-4xl font-black text-indigo-600">
                        R$ {{ number_format($roupa->preço, 2, ',', '.') }}
                    </span>
                    <p class="text-sm text-gray-400 mt-2 font-bold uppercase tracking-widest italic">
                        Estoque disponível: {{ $roupa->quantidade }} unidades
                    </p>
                </div>

                <div class="flex-grow">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2 border-b pb-2">Descrição do Produto</h3>
                    <p class="text-gray-600 leading-relaxed text-lg">
                        {{ $roupa->descrição }}
                    </p>
                </div>

                <div class="mt-10 flex gap-4">
                    {{-- FORMULÁRIO DE COMPRA --}}
                    <form action="{{ route('checkout.iniciar', $roupa->id) }}" method="POST" class="flex-grow">
                        @csrf
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-5 rounded-2xl font-black uppercase tracking-widest transition-all transform hover:scale-[1.02] shadow-xl flex items-center justify-center gap-3">
                            <i class="fas fa-shopping-cart"></i>
                            Comprar Agora
                        </button>
                    </form>
                    
                    @auth
                        <form action="{{ route('roupas.favorito', $roupa->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-5 bg-white border border-gray-200 rounded-2xl text-red-500 hover:bg-red-50 transition-colors shadow-sm">
                                <i class="{{ Auth::user()->favoritos->contains($roupa->id) ? 'fas' : 'far' }} fa-heart text-2xl"></i>
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection