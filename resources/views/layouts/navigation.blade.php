<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ url('/') }}">
                        @php
                            $config = session('sistema_configuracoes');
                            $logoPath = $config['logo'] ?? null;
                        @endphp
                        
                        <img src="{{ $logoPath ? asset('storage/'.$logoPath) : asset('img/gamesnest.jpg') }}" 
                             alt="Logo" 
                             style="width: 160px; height: 50px; object-fit: contain;" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center h-full">
                    <x-nav-link href="{{ url('/') }}" :active="request()->is('/')" class="!text-black !opacity-100 font-bold !border-none">
                        <i class="fas fa-gamepad text-lg"></i> <span>Catálogo</span>
                    </x-nav-link>
                    
                    <x-nav-link href="{{ route('carrinho.index') }}" :active="request()->routeIs('carrinho.index')" class="font-bold !text-gray-900 !border-none">
                        <i class="fas fa-shopping-cart text-indigo-600 text-lg"></i> <span>Meu Carrinho</span>
                    </x-nav-link>
                    
                    @auth
                        <x-nav-link href="{{ route('roupas.favoritos') }}" :active="request()->routeIs('roupas.favoritos')" class="font-bold !text-gray-900 !border-none">
                            <i class="fas fa-heart text-red-500"></i> <span>Meus Favoritos</span>
                        </x-nav-link>
                        
                        @if (Auth::user()->role === 'admin')
                            <x-nav-link :href="route('categorias.create')" :active="request()->routeIs('categorias.create')" class="!text-indigo-600 font-black !border-none">
                                <i class="fas fa-tags"></i> Adicionar Categoria
                            </x-nav-link>
                            <x-nav-link :href="route('roupas.create')" :active="request()->routeIs('roupas.create')" class="!text-green-600 font-black !border-none">
                                <i class="fas fa-plus-square"></i> Adicionar Produto
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white">
                                {{ Auth::user()->name }}
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
                        <a href="{{ route('login') }}" class="text-sm underline">Entrar</a>
                        <a href="{{ route('register') }}" class="text-sm text-indigo-600 font-bold underline">Cadastrar</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>