@extends('layouts.app')

@section('slot')
<div class="flex items-center justify-center bg-gray-100 py-10 min-h-screen">
    <div class="flex w-full max-w-4xl bg-white shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
        
        <div class="w-1/2 bg-indigo-600 p-12 flex flex-col justify-center text-white text-center">
            <h1 class="text-4xl font-black mb-4 uppercase tracking-tighter">Editar Grupo</h1>
            <p class="text-lg opacity-90">Altere o nome do console ou o ícone do menu.</p>
            <i class="fas fa-edit text-6xl mt-6 opacity-30"></i>
        </div>

        <div class="w-1/2 p-10 flex flex-col justify-center">
            {{-- Mostra erros de validação se houver --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-600 rounded-lg text-sm">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('categorias.update', $categoria->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="nome" :value="__('Nome do Console')" />
                    <x-text-input id="nome" class="block w-full mt-1" type="text" name="nome" value="{{ $categoria->nome }}" required autofocus />
                </div>

                <div>
                    <x-input-label for="icone" :value="__('Ícone (Ex: fas fa-gamepad)')" />
                    <x-text-input id="icone" class="block w-full mt-1" type="text" name="icone" value="{{ $categoria->icone }}" />
                </div>

                <div class="flex gap-3 pt-6">
                    <a href="{{ route('roupas.index') }}" class="w-1/3 bg-gray-200 text-gray-700 py-3 rounded-xl font-bold uppercase text-center hover:bg-gray-300 transition-all">
                        Voltar
                    </a>
                    <button type="submit" class="w-2/3 bg-indigo-600 text-white py-3 rounded-xl font-bold uppercase hover:bg-indigo-700 shadow-lg transition-all">
                        Salvar Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection