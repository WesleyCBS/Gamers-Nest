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
                @include('produtos.partials.card', ['produto' => $produto])
            @empty
                <div class="w-full">
                    <div class="text-center pt-6 pb-10">
                        <i class="fas fa-search-minus text-6xl text-gray-200 mb-4"></i>
                        @if(request('search'))
                            <p class="text-gray-500 font-medium">
                                Nenhum produto encontrado para "<span class="font-bold text-gray-700">{{ request('search') }}</span>".
                            </p>
                        @else
                            <p class="text-gray-400 font-medium">Nenhum produto encontrado.</p>
                        @endif
                    </div>

                    @if(!empty($relacionados) && $relacionados->isNotEmpty())
                        <div class="max-w-7xl mx-auto">
                            <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter text-center mb-1">
                                @if($tipoSugestao === 'relacionados')
                                    <i class="fas fa-lightbulb text-amber-400 mr-1"></i> Produtos relacionados
                                @else
                                    <i class="fas fa-fire text-orange-500 mr-1"></i> Talvez você goste
                                @endif
                            </h2>
                            <p class="text-center text-sm text-gray-400 mb-8">
                                {{ $tipoSugestao === 'relacionados' ? 'Encontramos produtos parecidos com o que você procurou.' : 'Confira os produtos mais vendidos da loja.' }}
                            </p>
                            <div class="flex flex-wrap justify-center gap-8">
                                @foreach($relacionados as $produto)
                                    @include('produtos.partials.card', ['produto' => $produto])
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-10 px-7 mb-10">
            {{ $produtos->appends(['search' => request('search')])->links() }}
        </div>
    </div>

    <script>
        document.querySelectorAll('.form-destaque').forEach(form => {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const botao = this.querySelector('button');
                botao.disabled = true;

                try {
                    const resposta = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });
                    const dados = await resposta.json();

                    if (resposta.ok && dados.success) {
                        const id = this.dataset.produto;
                        botao.className = (dados.destaque ? 'text-amber-400 hover:text-amber-500' : 'text-gray-300 hover:text-amber-400') + ' transition';
                        botao.title = dados.destaque ? 'Remover dos destaques' : 'Colocar em destaque';
                        document.getElementById('selo-destaque-' + id)?.classList.toggle('hidden', !dados.destaque);

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: dados.message,
                            showConfirmButton: false,
                            timer: 1800,
                        });
                    }
                } catch (erro) {
                    console.error(erro);
                }

                botao.disabled = false;
            });
        });

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