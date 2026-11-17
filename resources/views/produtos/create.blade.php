@extends('layouts.app')

@section('slot')
<div class="flex items-center justify-center bg-gray-100 py-10 min-h-screen">
    <div class="flex w-full max-w-5xl bg-white shadow-2xl rounded-3xl overflow-hidden">

        <div class="w-1/2 bg-indigo-600 p-12 flex flex-col justify-center text-white text-center">
            <div id="previa-vazia">
                <h1 class="text-4xl font-black mb-4 uppercase tracking-tighter">Novo Item</h1>
                <p class="text-lg opacity-80">Cadastre consoles ou jogos no seu catálogo gamer.</p>
                <div class="mt-8 text-6xl opacity-20"><i class="fas fa-gamepad"></i></div>
            </div>

            <div id="previa" class="hidden">
                <div class="bg-white rounded-3xl p-6 shadow-2xl">
                    <img id="previa-grande" src="" alt="Prévia da foto" class="w-full h-80 object-contain">
                </div>
                <p id="previa-legenda" class="mt-4 text-sm font-bold uppercase tracking-widest opacity-80">Capa do produto</p>
            </div>
        </div>

        <div class="w-1/2 p-10">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('produtos.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <x-input-label for="categoria_id" :value="__('Plataforma / Console')" class="font-bold" />
                        <a href="{{ route('categorias.create') }}" class="text-xs text-indigo-600 font-black hover:underline uppercase tracking-wider">
                            + Novo Console
                        </a>
                    </div>
                    <select name="categoria_id" id="categoria_id" class="block w-full p-2 border border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="" disabled {{ old('categoria_id') ? '' : 'selected' }}>Escolha o plataforma.</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" @selected(old('categoria_id') == $cat->id)>{{ $cat->nome }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('categoria_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="nome" :value="__('Nome do Produto')" class="font-bold" />
                    <x-text-input id="nome" class="block w-full mt-1 rounded-xl" type="text" name="nome" :value="old('nome')" placeholder="Ex: God of War Ragnarok" required />
                    <x-input-error :messages="$errors->get('nome')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="descrição" :value="__('Descrição')" class="font-bold" />
                    <textarea id="descrição" name="descrição" class="block w-full mt-1 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500" rows="2" placeholder="Detalhes sobre o jogo ou console..." required>{{ old('descrição') }}</textarea>
                    <x-input-error :messages="$errors->get('descrição')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="preço" :value="__('Preço (R$)')" class="font-bold" />
                        <x-text-input id="preço" class="block w-full mt-1 rounded-xl" type="number" step="0.01" name="preço" :value="old('preço')" placeholder="0,00" required />
                    </div>
                    <div>
                        <x-input-label for="quantidade" :value="__('Estoque')" class="font-bold" />
                        <x-text-input id="quantidade" class="block w-full mt-1 rounded-xl" type="number" name="quantidade" :value="old('quantidade')" placeholder="Qtd" required />
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center">
                        <x-input-label for="imagens" :value="__('Fotos do produto')" class="font-bold" />
                        <span id="contador-fotos" class="text-xs font-bold text-gray-400">0 / 5</span>
                    </div>
                    <p class="text-xs text-gray-400 mb-1">Até 5 fotos. A primeira será a capa do catálogo.</p>
                    <input id="imagens" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" type="file" name="imagens[]" accept="image/*" multiple required />
                    <div id="miniaturas" class="grid grid-cols-5 gap-2 mt-3"></div>
                    <x-input-error :messages="$errors->get('imagens')" class="mt-1" />
                    <x-input-error :messages="$errors->get('imagens.*')" class="mt-1" />
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest transition-all transform hover:scale-[1.02] active:scale-95 shadow-lg mt-4">
                    Salvar no Catálogo
                </button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const LIMITE = 5;
    const input = document.getElementById('imagens');
    const miniaturas = document.getElementById('miniaturas');
    const contador = document.getElementById('contador-fotos');
    const previa = document.getElementById('previa');
    const previaVazia = document.getElementById('previa-vazia');
    const previaGrande = document.getElementById('previa-grande');
    const legenda = document.getElementById('previa-legenda');

    let arquivos = [];
    let urls = [];
    let selecionada = 0;

    input.addEventListener('change', () => {
        const novos = Array.from(input.files).filter(f => f.type.startsWith('image/'));
        arquivos = arquivos.concat(novos);

        if (arquivos.length > LIMITE) {
            arquivos = arquivos.slice(0, LIMITE);
            Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Máximo de ' + LIMITE + ' fotos por produto.', showConfirmButton: false, timer: 2500 });
        }

        sincronizar();
        desenhar();
    });

    function sincronizar() {
        const transferencia = new DataTransfer();
        arquivos.forEach(f => transferencia.items.add(f));
        input.files = transferencia.files;
    }

    function desenhar() {
        urls.forEach(u => URL.revokeObjectURL(u));
        urls = arquivos.map(f => URL.createObjectURL(f));
        if (selecionada >= arquivos.length) selecionada = 0;

        miniaturas.innerHTML = '';
        urls.forEach((url, i) => {
            const item = document.createElement('div');
            item.className = 'relative aspect-square rounded-xl border-2 bg-white p-1 cursor-pointer transition ' + (i === selecionada ? 'border-indigo-500' : 'border-gray-200 hover:border-indigo-300');
            item.innerHTML =
                '<img src="' + url + '" class="w-full h-full object-contain rounded-lg">' +
                (i === 0 ? '<span class="absolute bottom-0 inset-x-0 bg-indigo-600 text-white text-[9px] font-bold uppercase text-center rounded-b-lg">Capa</span>' : '') +
                '<button type="button" class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center shadow" title="Remover"><i class="fas fa-times"></i></button>';

            item.addEventListener('click', () => { selecionada = i; desenhar(); });
            item.querySelector('button').addEventListener('click', (e) => {
                e.stopPropagation();
                arquivos.splice(i, 1);
                if (selecionada >= i && selecionada > 0) selecionada--;
                sincronizar();
                desenhar();
            });

            miniaturas.appendChild(item);
        });

        contador.textContent = arquivos.length + ' / ' + LIMITE;

        if (arquivos.length) {
            previa.classList.remove('hidden');
            previaVazia.classList.add('hidden');
            previaGrande.src = urls[selecionada];
            legenda.textContent = selecionada === 0 ? 'Capa do produto' : 'Foto ' + (selecionada + 1) + ' de ' + arquivos.length;
        } else {
            previa.classList.add('hidden');
            previaVazia.classList.remove('hidden');
        }
    }
})();
</script>
@endsection
