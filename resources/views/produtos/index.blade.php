@extends('layouts.app')

@section('slot')
    <div class="py-6">

        <!-- Carrossel de Banners Estilo Mercado Livre -->
        @php
            $pathSettings = storage_path('app/settings.json');
            $configApp = file_exists($pathSettings) ? json_decode(file_get_contents($pathSettings), true) : [];
            $bannersList = $configApp['banners'] ?? [];
        @endphp

        @if(count($bannersList) > 0)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="relative rounded-3xl overflow-hidden shadow-xl bg-gray-900 w-full aspect-[3/1]" 
                     x-data="{ 
                        activeSlide: 0, 
                        slides: {{ count($bannersList) }}, 
                        autoplay() { 
                            setInterval(() => { 
                                this.activeSlide = (this.activeSlide + 1) % this.slides; 
                            }, 4000); 
                        } 
                     }" 
                     x-init="autoplay()">
                    
                    <!-- Slides -->
                    <div class="relative w-full h-full">
                        @foreach($bannersList as $index => $banner)
                            <div x-show="activeSlide === {{ $index }}" 
                                 x-transition:enter="transition ease-out duration-500" 
                                 x-transition:enter-start="opacity-0 scale-105" 
                                 x-transition:enter-end="opacity-100 scale-100" 
                                 class="absolute inset-0 w-full h-full flex items-center justify-center bg-gray-900">
                                <img src="{{ Storage::url($banner) }}" class="w-full h-full object-contain">
                            </div>
                        @endforeach
                    </div>

                    <!-- Botões de Navegação (Setas) -->
                    <button @click="activeSlide = (activeSlide - 1 + slides) % slides" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 w-10 h-10 rounded-full flex items-center justify-center shadow-md transition-all cursor-pointer">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </button>
                    <button @click="activeSlide = (activeSlide + 1) % slides" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 w-10 h-10 rounded-full flex items-center justify-center shadow-md transition-all cursor-pointer">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </button>

                    <!-- Indicadores (Bolinhos) -->
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                        @foreach($bannersList as $index => $banner)
                            <button @click="activeSlide = {{ $index }}" 
                                    :class="{ 'bg-white w-6': activeSlide === {{ $index }}, 'bg-white/50 w-2.5': activeSlide !== {{ $index }}}" 
                                    class="h-2.5 rounded-full transition-all duration-300 cursor-pointer"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @auth
            @if (strtolower(Auth::user()->role) === 'admin')
                <h1 class="text-3xl font-black my-2 text-center uppercase tracking-tighter text-gray-800">Gerenciamento de Catálogo</h1>
                <p class="text-center mb-8 text-gray-500 font-medium">Você está no modo editor. Use a aba superior para adicionar itens.</p>
            @else
                <h1 class="text-3xl font-black my-2 text-center uppercase tracking-tighter text-gray-800">Produtos Disponíveis</h1>
                <p class="text-center mb-8 text-gray-500 font-medium">Explore as melhores ofertas da Gamers Nest!</p>
            @endif
        @else
            <h1 class="text-3xl font-black my-2 text-center uppercase tracking-tighter text-gray-800">Produtos Disponíveis</h1>
            <p class="text-center mb-8 text-gray-500 font-medium tracking-tight">Faça login para salvar seus favoritos!</p>
        @endauth

        <div class="max-w-7xl mx-auto px-7 mb-10">
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('produtos.index') }}" 
                   class="px-5 py-2 rounded-2xl font-bold uppercase text-xs tracking-widest transition-all shadow-sm {{ !isset($categoriaAtiva) ? 'bg-indigo-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50' }}">
                    <i class="fas fa-border-all mr-1"></i> Todos
                </a>

                @foreach($categorias as $cat)
                    <div class="flex items-center shadow-sm rounded-2xl overflow-hidden">
                        <a href="{{ route('produtos.categoria', $cat->slug) }}" 
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
                                    <form id="del-cat-{{ $cat->id }}" action="{{ route('categorias.destroy', $cat->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmarExclusao('del-cat-{{ $cat->id }}')" class="p-2 text-red-500 hover:text-red-700">
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

        <div class="flex flex-wrap justify-center gap-8 px-7">
            @forelse ($produtos as $produto)
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden w-64 flex flex-col h-[450px] transition-transform hover:scale-105 border border-gray-100 relative group">
                    <a href="{{ route('produtos.show', $produto->id) }}" class="absolute inset-0 z-10" title="Ver {{ $produto->nome }}"></a>
                    
                    <div class="h-48 overflow-hidden bg-gray-100 flex items-center justify-center">
                        <img src="/storage/products/{{ basename($produto->imagem) }}" alt="{{ $produto->nome }}" class="w-full h-full object-cover">
                    </div>

                    <div class="flex flex-col p-5 flex-grow">
                        <div class="flex justify-between items-start mb-2">
                            <h2 class="text-lg font-bold text-gray-800 leading-tight">{{ $produto->nome }}</h2>
                            @auth
                                @if(strtolower(Auth::user()->role) === 'admin')
                                    <div class="relative z-20 flex gap-2">
                                        <a href="{{ route('produtos.edit', $produto->id) }}" class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                        <form id="del-produto-{{ $produto->id }}" action="{{ route('produtos.destroy', $produto->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="button" onclick="confirmarExclusao('del-produto-{{ $produto->id }}')" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash-alt text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endauth
                        </div>
                        <p class="text-gray-500 text-sm line-clamp-2 mb-2">{{ $produto->descrição }}</p>
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mt-auto">Estoque: {{ $produto->quantidade }}</p>
                    </div>

                    <div class="bg-gray-50 px-5 py-4 border-t border-gray-100 flex justify-between items-center relative z-20">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Preço</span>
                            <span class="text-xl font-black text-indigo-600 leading-none">R$ {{ number_format($produto->preço, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            @auth
                                <form action="{{ route('produtos.favorito', $produto->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="hover:scale-125 transition-transform duration-200">
                                        <i class="{{ Auth::user()->favoritos->contains($produto->id) ? 'fas' : 'far' }} fa-heart text-red-500 text-xl"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="hover:scale-125 transition-transform duration-200">
                                    <i class="far fa-heart text-red-500 text-xl"></i>
                                </a>
                            @endauth
                            @auth
                                <form action="{{ route('carrinho.adicionar', $produto->id) }}" method="POST">
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
            {{ $produtos->appends(['search' => request('search')])->links() }}
        </div>
    </div>

    <script>
        function confirmarExclusao(formId) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Tem certeza que deseja excluir?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Sim',
                cancelButtonText: 'Não'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            })
        }
    </script>
@endsection