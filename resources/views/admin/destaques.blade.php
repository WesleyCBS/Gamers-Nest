@extends('layouts.app')

@section('slot')
<div class="max-w-5xl mx-auto py-12 px-4">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-2">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">
            <i class="fas fa-star mr-2 text-amber-400"></i> Produtos em Destaque
        </h1>
        <a href="{{ route('produtos.index') }}" class="text-indigo-600 font-bold hover:underline text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Ver na loja
        </a>
    </div>
    <p class="text-gray-500 mb-8">Os produtos em destaque aparecem primeiro na tela inicial, na ordem abaixo.</p>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-4 mb-6">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-xl rounded-3xl border border-gray-100 overflow-hidden mb-10">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Ordem na tela inicial</span>
            <span class="text-xs font-bold text-amber-500">{{ $destaques->count() }} {{ $destaques->count() === 1 ? 'destaque' : 'destaques' }}</span>
        </div>

        @forelse($destaques as $produto)
            <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-50 last:border-0">
                <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-black text-sm flex items-center justify-center shrink-0">
                    {{ $loop->iteration }}
                </span>

                <img src="/storage/products/{{ basename($produto->imagem) }}" alt="{{ $produto->nome }}" class="w-14 h-14 rounded-xl object-cover shadow-sm shrink-0">

                <div class="flex-1 min-w-0">
                    <span class="block font-bold text-gray-800 truncate">{{ $produto->nome }}</span>
                    <span class="block text-xs text-gray-400">
                        {{ $produto->categoria->nome ?? 'Sem categoria' }} · R$ {{ number_format($produto->preço, 2, ',', '.') }}
                    </span>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <form action="{{ route('admin.destaques.mover', [$produto->id, 'cima']) }}" method="POST" data-manter-scroll>
                        @csrf
                        <button type="submit" @disabled($loop->first)
                                class="w-9 h-9 rounded-xl border border-gray-200 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 disabled:opacity-30 disabled:cursor-not-allowed transition" title="Subir">
                            <i class="fas fa-arrow-up text-xs"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.destaques.mover', [$produto->id, 'baixo']) }}" method="POST" data-manter-scroll>
                        @csrf
                        <button type="submit" @disabled($loop->last)
                                class="w-9 h-9 rounded-xl border border-gray-200 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 disabled:opacity-30 disabled:cursor-not-allowed transition" title="Descer">
                            <i class="fas fa-arrow-down text-xs"></i>
                        </button>
                    </form>
                    <form action="{{ route('produtos.destaque', $produto->id) }}" method="POST" data-manter-scroll>
                        @csrf
                        <button type="submit" class="h-9 px-3 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white text-xs font-bold transition" title="Remover dos destaques">
                            <i class="fas fa-times mr-1"></i> Remover
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <i class="far fa-star text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Nenhum produto em destaque ainda. Escolha abaixo.</p>
            </div>
        @endforelse
    </div>

    <h2 class="text-lg font-black text-gray-800 uppercase tracking-tighter mb-4">Adicionar aos destaques</h2>

    @if($outros->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($outros as $produto)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 flex items-center gap-3">
                    <img src="/storage/products/{{ basename($produto->imagem) }}" alt="{{ $produto->nome }}" class="w-12 h-12 rounded-xl object-cover shrink-0">
                    <div class="flex-1 min-w-0">
                        <span class="block text-sm font-bold text-gray-800 truncate">{{ $produto->nome }}</span>
                        <span class="block text-xs text-gray-400 truncate">{{ $produto->categoria->nome ?? 'Sem categoria' }}</span>
                    </div>
                    <form action="{{ route('produtos.destaque', $produto->id) }}" method="POST" class="shrink-0" data-manter-scroll>
                        @csrf
                        <button type="submit" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-400 hover:text-white transition" title="Colocar em destaque">
                            <i class="fas fa-star text-sm"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-400">Todos os produtos já estão em destaque.</p>
    @endif
</div>

<script>
    (function () {
        const chave = 'scroll-destaques';
        try {
            const salvo = sessionStorage.getItem(chave);
            if (salvo !== null) {
                window.scrollTo(0, parseInt(salvo, 10));
                sessionStorage.removeItem(chave);
            }
        } catch (e) {}

        document.querySelectorAll('form[data-manter-scroll]').forEach(form => {
            form.addEventListener('submit', () => {
                try { sessionStorage.setItem(chave, window.scrollY); } catch (e) {}
            });
        });
    })();
</script>
@endsection
