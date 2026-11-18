@extends('layouts.app')

@section('slot')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <a href="{{ route('produtos.index') }}" onclick="return voltarAoCatalogo()" class="inline-flex items-center text-gray-500 hover:text-indigo-600 mb-8 font-bold transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Voltar ao Catálogo
        </a>

        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row border border-gray-100">
            
            @php $galeria = $produto->galeria; @endphp
            <div class="md:w-1/2 p-10 bg-white flex flex-col items-center justify-center gap-6" x-data="{ atual: @js($galeria[0] ?? '') }">
                <img src="{{ $galeria[0] ?? '' }}" :src="atual"
                     alt="{{ $produto->nome }}" 
                     class="max-h-[450px] w-full object-contain hover:scale-105 transition-transform duration-500 {{ $produto->esgotado ? 'grayscale opacity-50' : '' }}">

                @if(count($galeria) > 1)
                    <div class="flex flex-wrap justify-center gap-3">
                        @foreach($galeria as $url)
                            <button type="button" @click="atual = @js($url)"
                                    :class="atual === @js($url) ? 'ring-2 ring-indigo-500 opacity-100' : 'opacity-60 hover:opacity-100'"
                                    class="w-16 h-16 rounded-xl border border-gray-200 bg-white p-1 transition">
                                <img src="{{ $url }}" alt="Foto {{ $loop->iteration }} de {{ $produto->nome }}" class="w-full h-full object-contain">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="md:w-1/2 p-12 bg-gray-50 flex flex-col">
                <div class="mb-6">
                    <span class="px-4 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-black uppercase tracking-widest">
                        {{ $produto->categoria->nome ?? 'Produto' }}
                    </span>
                    <h1 class="text-4xl font-black text-gray-800 mt-4 leading-tight uppercase tracking-tighter">
                        {{ $produto->nome }}
                    </h1>
                </div>

                <div class="mb-8">
                    <span class="text-4xl font-black text-indigo-600">
                        R$ {{ number_format($produto->preço, 2, ',', '.') }}
                    </span>
                    @php $ehAdmin = Auth::check() && strtolower(Auth::user()->role) === 'admin'; @endphp
                    @if($produto->esgotado)
                        <span class="inline-block mt-3 bg-gray-800 text-white text-xs font-black uppercase tracking-widest px-4 py-1.5 rounded-full">
                            Esgotado
                        </span>
                    @elseif($ehAdmin && $produto->estoque_baixo)
                        <p class="mt-3 inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-xl">
                            <i class="fas fa-exclamation-triangle"></i> Estoque baixo: restam {{ $produto->quantidade }} {{ $produto->quantidade === 1 ? 'unidade' : 'unidades' }}
                        </p>
                    @else
                        <p class="text-sm text-gray-400 mt-2 font-bold uppercase tracking-widest italic">
                            Estoque disponível: {{ $produto->quantidade }} unidades
                        </p>
                    @endif
                </div>

                <div class="flex-grow">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2 border-b pb-2">Descrição do Produto</h3>
                    <p class="text-gray-600 leading-relaxed text-lg">
                        {{ $produto->descrição }}
                    </p>
                </div>

                <div class="mt-10 flex gap-4">
                    @auth
                        @if($produto->esgotado)
                        <button type="button" disabled class="flex-grow bg-gray-200 text-gray-400 py-5 rounded-2xl font-black uppercase tracking-widest flex items-center justify-center gap-3 cursor-not-allowed">
                            <i class="fas fa-ban"></i>
                            Produto esgotado
                        </button>
                        @else
                        <form action="{{ route('checkout.iniciar', $produto->id) }}" method="POST" class="flex-grow">
                            @csrf
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-5 rounded-2xl font-black uppercase tracking-widest transition-all transform hover:scale-[1.02] shadow-xl flex items-center justify-center gap-3">
                                <i class="fas fa-shopping-cart"></i>
                                Comprar Agora
                            </button>
                        </form>
                        @endif

                        <form action="{{ route('produtos.favorito', $produto->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-5 bg-white border border-gray-200 rounded-2xl text-red-500 hover:bg-red-50 transition-colors shadow-sm">
                                <i class="{{ Auth::user()->favoritos->contains($produto->id) ? 'fas' : 'far' }} fa-heart text-2xl"></i>
                            </button>
                        </form>
                    @else
                        @if($produto->esgotado)
                        <button type="button" disabled class="flex-grow bg-gray-200 text-gray-400 py-5 rounded-2xl font-black uppercase tracking-widest flex items-center justify-center gap-3 cursor-not-allowed">
                            <i class="fas fa-ban"></i>
                            Produto esgotado
                        </button>
                        @else
                        <a href="{{ route('login') }}" class="flex-grow bg-indigo-600 hover:bg-indigo-700 text-white py-5 rounded-2xl font-black uppercase tracking-widest transition-all transform hover:scale-[1.02] shadow-xl flex items-center justify-center gap-3">
                            <i class="fas fa-shopping-cart"></i>
                            Comprar Agora
                        </a>
                        @endif

                        <a href="{{ route('login') }}" class="p-5 bg-white border border-gray-200 rounded-2xl text-red-500 hover:bg-red-50 transition-colors shadow-sm">
                            <i class="far fa-heart text-2xl"></i>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function voltarAoCatalogo() {
        try {
            const endereco = sessionStorage.getItem('catalogo-url');
            if (endereco) {
                sessionStorage.setItem('catalogo-voltar', '1');
                window.location.href = endereco;
                return false;
            }
        } catch (e) {}
        return true;
    }
</script>
@endsection