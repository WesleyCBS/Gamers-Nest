@extends('layouts.app')

@section('slot')
<div class="flex items-center justify-center bg-gray-100 py-10 min-h-screen">
    <div class="flex w-full max-w-4xl bg-white shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
        
        <div class="w-1/2 bg-indigo-600 p-12 flex flex-col justify-center text-white text-center">
            <h1 class="text-4xl font-black mb-4 uppercase tracking-tighter">Editar Item</h1>
            <p class="text-lg opacity-90">Atualize as informações do seu produto no catálogo.</p>
            <i class="fas fa-edit text-6xl mt-6 opacity-30"></i>
        </div>

        <div class="w-1/2 p-10">
            <form method="POST" action="{{ route('roupas.update', $roupa->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT') 

                <div>
                    <x-input-label for="categoria_id" :value="__('Plataforma/Console')" />
                    <select name="categoria_id" id="categoria_id" class="block w-full mt-1 border-gray-300 rounded-xl focus:ring-indigo-500" required>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ $roupa->categoria_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="nome" :value="__('Nome do Item')" />
                    <x-text-input id="nome" class="block w-full mt-1" type="text" name="nome" value="{{ $roupa->nome }}" required />
                </div>

                <div>
                    <x-input-label for="descrição" :value="__('Descrição')" />
                    <textarea id="descrição" name="descrição" class="block w-full mt-1 border-gray-300 rounded-xl focus:ring-indigo-500" rows="3">{{ $roupa->descrição }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="preço" :value="__('Preço (R$)')" />
                        <x-text-input id="preço" class="block w-full mt-1" type="number" step="0.01" name="preço" value="{{ $roupa->preço }}" required />
                    </div>
                    <div>
                        <x-input-label for="quantidade" :value="__('Estoque')" />
                        <x-text-input id="quantidade" class="block w-full mt-1" type="number" name="quantidade" value="{{ $roupa->quantidade }}" required />
                    </div>
                </div>

                <div>
                    <x-input-label for="imagem" :value="__('Trocar Foto (Opcional)')" />
                    <div class="mt-1 flex items-center gap-4">
                        <img src="{{ asset($roupa->imagem) }}" class="w-12 h-12 object-cover rounded-lg border">
                        <input type="file" name="imagem" class="block w-full text-sm text-gray-500" />
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('roupas.index') }}" class="w-1/3 bg-gray-200 text-gray-700 py-3 rounded-xl font-bold uppercase text-center hover:bg-gray-300 transition-all">
                        Cancelar
                    </a>
                    <button type="submit" class="w-2/3 bg-indigo-600 text-white py-3 rounded-xl font-bold uppercase hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection