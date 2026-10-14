<nav x-data="{ open: false }" class="bg-gray-900 border-b border-gray-800 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Lado Esquerdo: Logo, Barra de Pesquisa e Atalhos de Admin -->
            <div class="flex items-center space-x-6 flex-1">
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
                                 style="width: 150px; height: 50px; object-fit: contain;" />
                        @else
                            <img src="{{ asset('img/gamesnest.jpg') }}" 
                                 alt="Game Nest" 
                                 style="width: 150px; height: 50px; object-fit: contain;" />
                        @endif
                    </a>
                </div>

                <!-- Barra de Pesquisa Puxada para a Esquerda -->
                <div class="hidden md:flex items-center flex-1 max-w-sm">
                    <form action="{{ route('produtos.index') }}" method="GET" class="w-full relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="O que você está procurando?" 
                               class="w-full bg-white text-gray-800 text-xs rounded-xl pl-9 pr-4 py-2 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition placeholder:text-gray-400">
                        <button type="submit" class="absolute left-3 top-2.5 text-gray-400 hover:text-indigo-600">
                            <i class="fas fa-search text-xs"></i>
                        </button>
                    </form>
                </div>

                <div class="hidden sm:flex items-center space-x-4">
                    @auth
                        @if (Auth::user()->role === 'admin')
                            <x-nav-link :href="route('categorias.create')" :active="request()->routeIs('categorias.create')" class="!text-indigo-400 font-black !border-none">
                                <i class="fas fa-tags mr-1.5"></i> Categoria
                            </x-nav-link>
                            <x-nav-link :href="route('produtos.create')" :active="request()->routeIs('produtos.create')" class="!text-green-400 font-black !border-none">
                                <i class="fas fa-plus-square mr-1.5"></i> Produto
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Lado Direito: Favoritos, Carrinho e Perfil -->
            <div class="hidden sm:flex sm:items-center sm:space-x-6">
                @auth
                    @php 
                        $favoritosCount = Auth::user()->favoritos ? Auth::user()->favoritos->count() : 0; 
                    @endphp
                    <a href="{{ route('produtos.favoritos') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-gray-200 hover:text-white bg-gray-800 hover:bg-gray-700 rounded-xl transition whitespace-nowrap shadow-sm border border-gray-700 relative">
                        <i class="fas fa-heart text-red-500"></i>
                        <span>Favoritos</span>
                        @if($favoritosCount > 0)
                            <span class="ml-1 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center">
                                {{ $favoritosCount }}
                            </span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-gray-200 hover:text-white bg-gray-800 hover:bg-gray-700 rounded-xl transition whitespace-nowrap shadow-sm border border-gray-700">
                        <i class="fas fa-heart text-red-500"></i>
                        <span>Favoritos</span>
                    </a>
                @endauth

                <a href="{{ route('carrinho.index') }}" class="inline-flex items-center font-bold text-gray-200 hover:text-white transition">
                    <div class="flex items-center relative">
                        <i class="fas fa-shopping-cart text-indigo-400 text-lg mr-1.5"></i>
                        
                        @php $count = session()->has('carrinho') ? count(session('carrinho')) : 0; @endphp
                        
                        @if($count > 0)
                            <span class="ml-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center animate-bounce">
                                {{ $count }}
                            </span>
                        @endif
                        
                        <span class="ml-2 hidden lg:inline">Meu Carrinho</span>
                    </div>
                </a>

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
                                <span class="hidden lg:inline">{{ Auth::user()->name }}</span>
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
                    <div class="space-x-3">
                        <a href="{{ route('login') }}" class="text-sm text-gray-300 hover:text-white underline">Entrar</a>
                        <a href="{{ route('register') }}" class="text-sm text-indigo-400 font-bold underline">Cadastrar</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>