@extends('layouts.app')

@section('slot')
<div class="flex items-center justify-center bg-gray-100 py-10 min-h-screen">
    <div class="flex w-full max-w-5xl bg-white shadow-2xl rounded-3xl overflow-hidden">
        
        <div class="w-1/2 bg-indigo-600 p-12 flex flex-col justify-center text-white text-center">
            <h1 class="text-4xl font-black mb-4 uppercase tracking-tighter">Novo Console</h1>
            <p class="text-lg opacity-80">Cadastre novas plataformas para organizar seu catálogo de jogos.</p>
            <div class="mt-8 text-6xl opacity-20"><i class="fas fa-tags"></i></div>
        </div>

        <div class="w-1/2 p-10">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('categorias.store') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="nome" :value="__('Nome da Plataforma / Console')" class="font-bold" />
                    <x-text-input id="nome" class="block w-full mt-1 rounded-xl p-4 border-gray-300 focus:ring-indigo-500 shadow-sm" 
                        type="text" 
                        name="nome" 
                        placeholder="Ex: PlayStation 5, Xbox Series, Nintendo..." 
                        :value="old('nome')"
                        required 
                        autofocus />
                    <x-input-error :messages="$errors->get('nome')" class="mt-1" />
                </div>

                <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 flex items-start gap-3">
                    <i class="fas fa-info-circle text-indigo-600 mt-1"></i>
                    <p class="text-sm text-indigo-700 leading-relaxed">
                        Ao salvar, este console aparecerá automaticamente no menu de seleção quando você for cadastrar ou editar um item.
                    </p>
                </div>

                <div class="pt-4 space-y-4">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest transition-all transform hover:scale-[1.02] active:scale-95 shadow-lg">
                        Confirmar Cadastro
                    </button>

                    <a href="{{ route('roupas.create') }}" class="block text-center w-full text-gray-500 font-bold hover:underline py-2 uppercase text-xs tracking-widest">
                        Cancelar e Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection