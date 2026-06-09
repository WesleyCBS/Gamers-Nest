@extends('layouts.app')

@section('slot')
    <div class="py-6">
        @auth
            @if (strtolower(Auth::user()->role) === 'admin')
                <h1 class="text-3xl font-black my-2 text-center uppercase tracking-tighter text-gray-800">Gerenciamento de Catálogo</h1>
                <p class="text-center mb-8 text-gray-500 font-medium">Você está no modo editor. Use a aba superior para adicionar itens.</p>
            @else
                <h1 class="text-3xl font-black my-2 text-center uppercase tracking-tighter text-gray-800">Produtos Disponíveis</h1>
                <p class="text-center mb-8 text-gray-500 font-medium">Explore as melhores ofertas da Games Nest!</p>
            @endif
        @else
            <h1 class="text-3xl font-black my-2 text-center uppercase tracking-tighter text-gray-800">Produtos Disponíveis</h1>
            <p class="text-center mb-8 text-gray-500 font-medium tracking-tight">Faça login para salvar seus favoritos!</p>
        @endauth

        {{-- BARRA DE PESQUISA DINÂMICA --}}
        <div class="max-w-md mx-auto mb-10 px-7">
            <form action="{{ url()->current() }}" method="GET" class="relative group">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ isset($categoriaAtiva) ? 'Buscar em ' . $categoriaAtiva->nome . '...' : 'O que você está procurando?' }}" 
                    class="w-full pl-12 pr-10 py-3 rounded-2xl border-none shadow-md focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-gray-400 font-medium text-gray-700">
                
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-600 transition-colors">
                    <i class="fas fa-search text-lg"></i>
                </div>

                @if(request('search'))
                    <a href="{{ url()->current() }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times-circle"></i>
                    </a>
                @endif
            </form>
        </div>

        {{-- FILTROS DE CATEGORIA --}}
        <div class="max-w-7xl mx-auto px-7 mb-10">
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('roupas.index') }}" 
                   class="px-5 py-2 rounded-2xl font-bold uppercase text-xs tracking-widest transition-all shadow-sm {{ !isset($categoriaAtiva) ? 'bg-indigo-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50' }}">
                    <i class="fas fa-border-all mr-1"></i> Todos
                </a>

                @foreach($categorias as $cat)
                    <div class="flex items-center shadow-sm rounded-2xl overflow-hidden">
                        <a href="{{ route('roupas.categoria', $cat->slug) }}" 
                           class="px-5 py-2 font-bold uppercase text-xs tracking-widest transition-all flex items-center gap-2 {{ (isset($categoriaAtiva) && $categoriaAtiva->id == $cat->id) ? 'bg-indigo-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50 border-r border-gray-100' }}">
                            <i class="{{ $cat->icone }}"></i>
                            {{ $cat->nome }}
                        </a>
                        @auth
                            @if(strtolower(Auth::user()->role) === 'admin')
                                <div class="flex bg-white h-full items-center px-1 border-l border-gray-100">
                                    <a href="{{ route('categorias.edit', $cat->id) }}" class="p-2 text-blue-500 hover:text-blue-700 transition-colors" title="Editar">
                                        <i class="fas fa-edit text-[10px]"></i>
                                    </a>
                                    <form action="{{ route('categorias.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Excluir?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash text-[10px]"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </div>
                @endforeach
            </div>
        </div>

        {{-- LISTAGEM DE PRODUTOS --}}
        <div class="flex flex-wrap justify-center gap-8 px-7">
            @forelse ($roupas as $roupa)
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden w-64 flex flex-col h-[450px] transition-transform hover:scale-105 border border-gray-100 relative group">
                    <a href="{{ route('roupas.show', $roupa->id) }}" class="absolute inset-0 z-10" title="Ver {{ $roupa->nome }}"></a>

                    <div class="h-48 overflow-hidden bg-gray-100 flex items-center justify-center">
                        @php
                            // Garante que pega apenas o nome do arquivo, ex: "foto.jpg"
                            $nomeArquivo = basename($roupa->imagem);
                        @endphp
                        {{--Chama a nossa rota personalizada--}}
                        <img src="/storage/products/{{ $nomeArquivo }}" alt="{{ $roupa->nome }}" class="w-full h-full object-cover">
                    </div>

                    <div class="flex flex-col p-5 flex-grow">
                        <div class="flex justify-between items-start mb-2">
                            <h2 class="text-lg font-bold text-gray-800 leading-tight">{{ $roupa->nome }}</h2>
                            
                            @auth
                                @if(strtolower(Auth::user()->role) === 'admin')
                                    <div class="relative z-20 flex gap-2">
                                        <a href="{{ route('roupas.edit', $roupa->id) }}" class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                        <form action="{{ route('roupas.destroy', $roupa->id) }}" method="POST" onsubmit="return confirm('Excluir item?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash-alt text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endauth
                        </div>

                        <p class="text-gray-500 text-sm line-clamp-2 mb-2">{{ $roupa->descrição }}</p>
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mt-auto">Estoque: {{ $roupa->quantidade }}</p>
                    </div>

                    <div class="bg-gray-50 px-5 py-4 border-t border-gray-100 flex justify-between items-center relative z-20">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Preço</span>
                            <span class="text-xl font-black text-indigo-600 leading-none">R$ {{ number_format($roupa->preço, 2, ',', '.') }}</span>
                        </div>

                        <div class="flex items-center gap-3">
                            @auth
                                <form action="{{ route('roupas.favorito', $roupa->id) }}" method="POST" class="flex items-center">
                                    @csrf
                                    <button type="submit" class="hover:scale-125 transition-transform duration-200">
                                        <i class="{{ Auth::user()->favoritos->contains($roupa->id) ? 'fas' : 'far' }} fa-heart text-red-500 text-xl"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="text-gray-300 hover:text-red-500 transition-colors">
                                    <i class="far fa-heart text-xl"></i>
                                </a>
                            @endauth

                            {{-- TRAVA DE LOGIN NO CARRINHO --}}
                            @auth
                                <form action="{{ route('carrinho.adicionar', $roupa->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-indigo-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-indigo-700 hover:scale-110 transition-all shadow-md active:scale-95">
                                        <i class="fas fa-cart-plus text-sm"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="bg-indigo-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-indigo-700 hover:scale-110 transition-all shadow-md active:scale-95">
                                    <i class="fas fa-cart-plus text-sm"></i>
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 w-full">
                    <i class="fas fa-search-minus text-6xl text-gray-200 mb-4"></i>
                    <p class="text-gray-400 font-medium">Nenhum produto encontrado para sua busca.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-10 px-7 mb-10">
            {{ $roupas->appends(['search' => request('search')])->links() }}
        </div>
    </div>
@endsection