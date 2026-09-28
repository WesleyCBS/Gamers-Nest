<nav x-data="{ open: false }" class="bg-gray-900 border-b border-gray-800 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ url('/') }}">
                        @php
                            $path = storage_path('app/settings.json');
                            $config = file_exists($path) ? json_decode(file_get_contents($path), true) : null;
                            $logoPath = $config['logo'] ?? null;
                        @endphp
                        
                        @if($logoPath)
                            <img src="{{ url('/storage/' . $logoPath) . '?v=' . time() }}" 
                                 alt="Logo da Loja" 
                                 style="width: 160px; height: 50px; object-fit: contain;" />
                        @else
                            <img src="{{ asset('img/gamesnest.jpg') }}" 
                                 alt="Game Nest" 
                                 style="width: 160px; height: 50px; object-fit: contain;" />
                        @endif
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center h-full">
                    <x-nav-link href="{{ url('/') }}" :active="request()->is('/')" class="!text-gray-200 !opacity-100 font-bold !border-none">
                        <i class="fas fa-gamepad text-lg mr-1.5"></i> <span>Catálogo</span>
                    </x-nav-link>
                    
                    <x-nav-link href="{{ route('carrinho.index') }}" :active="request()->routeIs('carrinho.index')" class="font-bold !text-gray-200 !border-none">
                        <div class="flex items-center relative">
                            <i class="fas fa-shopping-cart text-indigo-400 text-lg mr-1.5"></i>
                            
                            @php $count = session()->has('carrinho') ? count(session('carrinho')) : 0; @endphp
                            
                            @if($count > 0)
                                <span class="ml-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center animate-bounce">
                                    {{ $count }}
                                </span>
                            @endif
                            
                            <span class="ml-2">Meu Carrinho</span>
                        </div>
                    </x-nav-link>
                    
                    @auth
                        <x-nav-link href="{{ route('produtos.favoritos') }}" :active="request()->routeIs('produtos.favoritos')" class="font-bold !text-gray-200 !border-none">
                            <i class="fas fa-heart text-red-500 mr-1.5"></i> <span>Meus Favoritos</span>
                        </x-nav-link>
                        
                        @if (Auth::user()->role === 'admin')
                            <x-nav-link :href="route('categorias.create')" :active="request()->routeIs('categorias.create')" class="!text-indigo-400 font-black !border-none">
                                <i class="fas fa-tags mr-1.5"></i> Adicionar Categoria
                            </x-nav-link>
                            <x-nav-link :href="route('produtos.create')" :active="request()->routeIs('produtos.create')" class="!text-green-400 font-black !border-none">
                                <i class="fas fa-plus-square mr-1.5"></i> Adicionar Produto
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-300 bg-gray-900 hover:text-white transition rounded-full focus:outline-none">
                                @if(Auth::user()->foto)
                                    <img src="{{ Storage::url(Auth::user()->foto) }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover border border-indigo-500 shadow-sm">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs uppercase shadow-sm">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                @endif
                                <span>{{ Auth::user()->name }}</span>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            @if (Auth::user()->role === 'admin')
                                <x-dropdown-link :href="route('admin.configuracoes')" class="text-indigo-600 font-bold">
                                    Configurações
                                </x-dropdown-link>
                            @endif
                            <x-dropdown-link :href="route('profile.edit')">Meu Perfil</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Sair</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="space-x-4">
                        <a href="{{ route('login') }}" class="text-sm text-gray-300 hover:text-white underline">Entrar</a>
                        <a href="{{ route('register') }}" class="text-sm text-indigo-400 font-bold underline">Cadastrar</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>