@extends('layouts.app')

@section('slot')
<div class="flex items-center justify-center bg-gray-100 py-10 min-h-screen">
    <div class="flex w-full max-w-5xl bg-white shadow-2xl rounded-3xl overflow-hidden">
        
        <div class="w-1/2 bg-indigo-600 p-12 flex flex-col justify-center text-white text-center">
            <h1 class="text-4xl font-black mb-4 uppercase tracking-tighter">Novo Item</h1>
            <p class="text-lg opacity-80">Cadastre consoles ou jogos no seu catálogo gamer.</p>
            <div class="mt-8 text-6xl opacity-20"><i class="fas fa-gamepad"></i></div>
        </div>

        <div class="w-1/2 p-10">
            {{-- Exibição de Mensagens de Erro ou Sucesso --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('roupas.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <x-input-label for="categoria_id" :value="__('Plataforma / Console')" class="font-bold" />
                        <a href="{{ route('categorias.create') }}" class="text-xs text-indigo-600 font-black hover:underline uppercase tracking-wider">
                            + Novo Console
                        </a>
                    </div>
                    <select name="categoria_id" id="categoria_id" class="block w-full p-2 border border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="" disabled selected>Escolha o plataforma.</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('categoria_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="nome" :value="__('Nome do Produto')" class="font-bold" />
                    <x-text-input id="nome" class="block w-full mt-1 rounded-xl" type="text" name="nome" placeholder="Ex: God of War Ragnarok" required />
                    <x-input-error :messages="$errors->get('nome')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="descrição" :value="__('Descrição')" class="font-bold" />
                    <textarea id="descrição" name="descrição" class="block w-full mt-1 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500" rows="2" placeholder="Detalhes sobre o jogo ou console..." required></textarea>
                    <x-input-error :messages="$errors->get('descrição')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="preço" :value="__('Preço (R$)')" class="font-bold" />
                        <x-text-input id="preço" class="block w-full mt-1 rounded-xl" type="number" step="0.01" name="preço" placeholder="0,00" required />
                    </div>
                    <div>
                        <x-input-label for="quantidade" :value="__('Estoque')" class="font-bold" />
                        <x-text-input id="quantidade" class="block w-full mt-1 rounded-xl" type="number" name="quantidade" placeholder="Qtd" required />
                    </div>
                </div>

                <div>
                    <x-input-label for="imagem" :value="__('Capa / Imagem')" class="font-bold" />
                    <input id="imagem" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" type="file" name="imagem" required />
                    <x-input-error :messages="$errors->get('imagem')" class="mt-1" />
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest transition-all transform hover:scale-[1.02] active:scale-95 shadow-lg mt-4">
                    Salvar no Catálogo
                </button>
            </form>
        </div>
    </div>
</div>
@endsection