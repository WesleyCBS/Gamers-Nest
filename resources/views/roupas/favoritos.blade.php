@extends('layouts.app')

@section('slot')
    <div class="py-6">
        <h1 class="text-3xl font-black my-6 text-center uppercase tracking-tighter text-gray-800">Meus Favoritos</h1>

        @if ($roupasFavoritas->isEmpty())
            <div class="text-center py-20 w-full">
                <i class="fas fa-heart-broken text-6xl text-gray-200 mb-4"></i>
                <p class="text-gray-400 font-medium">Você ainda não tem nenhum item favoritado.</p>
                <a href="{{ route('roupas.index') }}" class="mt-4 inline-block text-indigo-600 font-bold underline">Voltar para a loja</a>
            </div>
        @else
            <div class="flex flex-wrap justify-center gap-8 px-7">
                @foreach ($roupasFavoritas as $roupa)
                    {{-- FIX: h-[450px] garante que todos os cards tenham o mesmo tamanho --}}
                    <div class="bg-white shadow-xl rounded-3xl overflow-hidden w-64 flex flex-col h-[450px] transition-transform hover:scale-105 border border-gray-100 relative group">
                        
                        {{-- Link para detalhes --}}
                        <a href="{{ route('roupas.show', $roupa->id) }}" class="absolute inset-0 z-10" title="Ver {{ $roupa->nome }}"></a>

                        {{-- FIX: h-48 e object-cover para padronizar as imagens --}}
                        <div class="h-48 overflow-hidden">
                            <img src="{{ Storage::url(str_replace('storage/', '', $roupa->imagem)) }}" alt="{{ $roupa->nome }}" class="w-full h-full object-cover">
                        </div>

                        <div class="flex flex-col p-5 flex-grow">
                            <div class="flex justify-between items-start mb-2">
                                {{-- FIX: h-14 e line-clamp-2 para alinhar títulos de tamanhos diferentes --}}
                                <h2 class="text-lg font-bold text-gray-800 leading-tight h-14 line-clamp-2">{{ $roupa->nome }}</h2>
                            </div>

                            <p class="text-gray-500 text-sm line-clamp-2 mb-2">{{ $roupa->descrição }}</p>
                            <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mt-auto">Estoque: {{ $roupa->quantidade }}</p>
                        </div>

                        {{-- RODAPÉ PADRONIZADO: Preço + Favorito + Carrinho --}}
                        <div class="bg-gray-50 px-5 py-4 border-t border-gray-100 flex justify-between items-center relative z-20">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Preço</span>
                                <span class="text-xl font-black text-indigo-600 leading-none">R$ {{ number_format($roupa->preço, 2, ',', '.') }}</span>
                            </div>

                            <div class="flex items-center gap-3">
                                {{-- BOTÃO DE FAVORITOS (Para remover) --}}
                                <form action="{{ route('roupas.favorito', $roupa->id) }}" method="POST" class="flex items-center">
                                    @csrf
                                    <button type="submit" class="hover:scale-125 transition-transform duration-200">
                                        <i class="fas fa-heart text-red-500 text-xl"></i>
                                    </button>
                                </form>

                                {{-- BOTÃO CARRINHO --}}
                                <form action="{{ route('carrinho.adicionar', $roupa->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-indigo-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-indigo-700 hover:scale-110 transition-all shadow-md active:scale-95">
                                        <i class="fas fa-cart-plus text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection