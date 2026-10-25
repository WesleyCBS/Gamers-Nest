<div class="bg-white shadow-xl rounded-3xl overflow-hidden w-64 flex flex-col h-[450px] transition-transform hover:scale-105 border border-gray-100 relative group">
    <a href="{{ route('produtos.show', $produto->id) }}" class="absolute inset-0 z-10" title="Ver {{ $produto->nome }}"></a>

    <div class="h-48 overflow-hidden bg-white flex items-center justify-center relative">
        @if(Auth::check() && strtolower(Auth::user()->role) === 'admin')
        <span id="selo-destaque-{{ $produto->id }}" class="{{ $produto->destaque ? '' : 'hidden' }} z-[5] absolute top-3 left-3 bg-amber-400 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-md">
                <i class="fas fa-star mr-1"></i> Destaque
            </span>
        @endif
        <img src="/storage/products/{{ basename($produto->imagem) }}" alt="{{ $produto->nome }}" class="w-full h-full object-contain p-3"></div>

    <div class="flex flex-col p-5 flex-grow">
        <div class="flex justify-between items-start mb-2">
            <h2 class="text-lg font-bold text-gray-800 leading-tight">{{ $produto->nome }}</h2>
            @auth
                @if(strtolower(Auth::user()->role) === 'admin')
                    <div class="relative z-20 flex gap-2">
                        <form action="{{ route('produtos.destaque', $produto->id) }}" method="POST" class="form-destaque" data-produto="{{ $produto->id }}">
                            @csrf
                            <button type="submit" class="{{ $produto->destaque ? 'text-amber-400 hover:text-amber-500' : 'text-gray-300 hover:text-amber-400' }} transition" title="{{ $produto->destaque ? 'Remover dos destaques' : 'Colocar em destaque' }}">
                                <i class="fas fa-star text-sm"></i>
                            </button>
                        </form>
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
