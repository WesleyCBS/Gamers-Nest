@extends('layouts.app')

@section('slot')
@php
    $capaUrl = $produto->imagem ? Storage::url(str_replace('storage/', '', $produto->imagem)) : '';
@endphp
<div class="flex items-center justify-center bg-gray-100 py-10 min-h-screen">
    <div class="flex w-full max-w-5xl bg-white shadow-2xl rounded-3xl overflow-hidden border border-gray-100">

        <div class="w-1/2 bg-indigo-600 p-12 flex flex-col justify-center text-white text-center">
            <h1 class="text-4xl font-black mb-6 uppercase tracking-tighter">Editar Item</h1>
            <div class="bg-white rounded-3xl p-6 shadow-2xl">
                <img id="previa-grande" src="{{ $capaUrl }}" alt="{{ $produto->nome }}" class="w-full h-72 object-contain">
            </div>
            <p id="previa-legenda" class="mt-4 text-sm font-bold uppercase tracking-widest opacity-80">Capa do produto</p>
        </div>

        <div class="w-1/2 p-10">
            <form method="POST" action="{{ route('produtos.update', $produto->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="categoria_id" :value="__('Plataforma/Console')" />
                    <select name="categoria_id" id="categoria_id" class="block w-full mt-1 border-gray-300 rounded-xl focus:ring-indigo-500" required>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id', $produto->categoria_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="nome" :value="__('Nome do Item')" />
                    <x-text-input id="nome" class="block w-full mt-1" type="text" name="nome" value="{{ old('nome', $produto->nome) }}" required />
                </div>

                <div>
                    <x-input-label for="descrição" :value="__('Descrição')" />
                    <textarea id="descrição" name="descrição" class="block w-full mt-1 border-gray-300 rounded-xl focus:ring-indigo-500" rows="3">{{ old('descrição', $produto->descrição) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="preço" :value="__('Preço (R$)')" />
                        <x-text-input id="preço" class="block w-full mt-1" type="number" step="0.01" name="preço" value="{{ old('preço', $produto->preço) }}" required />
                    </div>
                    <div>
                        <x-input-label for="quantidade" :value="__('Estoque')" />
                        <x-text-input id="quantidade" class="block w-full mt-1" type="number" name="quantidade" value="{{ old('quantidade', $produto->quantidade) }}" required />
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center">
                        <x-input-label :value="__('Fotos do produto')" />
                        <span id="contador-fotos" class="text-xs font-bold text-gray-400"></span>
                    </div>
                    <p class="text-xs text-gray-400 mb-2">Até 5 fotos. Clique numa foto para ver em tamanho grande, ou no X para remover.</p>

                    <div id="fotos-atuais" class="grid grid-cols-5 gap-2">
                        <div class="foto-item relative aspect-square rounded-xl border-2 border-indigo-500 bg-white p-1 cursor-pointer" data-url="{{ $capaUrl }}" data-legenda="Capa do produto">
                            <img id="miniatura-capa" src="{{ $capaUrl }}" class="w-full h-full object-contain rounded-lg">
                            <span class="absolute bottom-0 inset-x-0 bg-indigo-600 text-white text-[9px] font-bold uppercase text-center rounded-b-lg">Capa</span>
                        </div>

                        @foreach($produto->imagens as $foto)
                            <label class="foto-item foto-extra relative aspect-square rounded-xl border-2 border-gray-200 bg-white p-1 cursor-pointer" data-url="{{ $foto->url }}" data-legenda="Foto {{ $loop->iteration + 1 }}">
                                <input type="checkbox" name="remover_imagens[]" value="{{ $foto->id }}" class="hidden remover-checkbox">
                                <img src="{{ $foto->url }}" class="w-full h-full object-contain rounded-lg">
                                <span class="selo-removida hidden absolute inset-0 bg-red-500/70 rounded-lg text-white text-[9px] font-bold uppercase flex items-center justify-center text-center">Será removida</span>
                                <button type="button" class="botao-remover absolute -top-2 -right-2 w-5 h-5 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center shadow" title="Remover">
                                    <i class="fas fa-times"></i>
                                </button>
                            </label>
                        @endforeach
                    </div>

                    <div id="miniaturas-novas" class="grid grid-cols-5 gap-2 mt-2"></div>

                    <div class="grid grid-cols-1 gap-3 mt-3">
                        <div>
                            <span class="block text-xs font-bold text-gray-500 mb-1">Trocar a capa (opcional)</span>
                            <input id="nova-capa" type="file" name="imagem" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-500 mb-1">Adicionar mais fotos</span>
                            <input id="novas-fotos" type="file" name="imagens[]" accept="image/*" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
                        </div>
                    </div>

                    <x-input-error :messages="$errors->get('imagem')" class="mt-1" />
                    <x-input-error :messages="$errors->get('imagens')" class="mt-1" />
                    <x-input-error :messages="$errors->get('imagens.*')" class="mt-1" />
                </div>

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('produtos.index') }}" class="w-1/3 bg-gray-200 text-gray-700 py-3 rounded-xl font-bold uppercase text-center hover:bg-gray-300 transition-all">
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

<script>
(function () {
    const LIMITE_EXTRAS = 4;
    const previaGrande = document.getElementById('previa-grande');
    const legenda = document.getElementById('previa-legenda');
    const contador = document.getElementById('contador-fotos');
    const inputNovas = document.getElementById('novas-fotos');
    const inputCapa = document.getElementById('nova-capa');
    const areaNovas = document.getElementById('miniaturas-novas');
    const miniaturaCapa = document.getElementById('miniatura-capa');

    let novas = [];
    let urlsNovas = [];

    function mostrar(url, texto) {
        previaGrande.src = url;
        legenda.textContent = texto;
    }

    function extrasMantidas() {
        return Array.from(document.querySelectorAll('.foto-extra')).filter(el => !el.querySelector('.remover-checkbox').checked).length;
    }

    function atualizarContador() {
        contador.textContent = (1 + extrasMantidas() + novas.length) + ' / 5';
    }

    document.querySelectorAll('.foto-item').forEach(el => {
        el.addEventListener('click', (e) => {
            if (e.target.closest('.botao-remover')) return;
            e.preventDefault();
            mostrar(el.dataset.url, el.dataset.legenda);
        });
    });

    document.querySelectorAll('.foto-extra').forEach(el => {
        el.querySelector('.botao-remover').addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const caixa = el.querySelector('.remover-checkbox');

            if (caixa.checked && extrasMantidas() + novas.length >= LIMITE_EXTRAS) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Máximo de 5 fotos por produto.', showConfirmButton: false, timer: 2500 });
                return;
            }

            caixa.checked = !caixa.checked;
            el.querySelector('.selo-removida').classList.toggle('hidden', !caixa.checked);
            el.querySelector('.botao-remover i').className = caixa.checked ? 'fas fa-undo' : 'fas fa-times';
            el.querySelector('.botao-remover').title = caixa.checked ? 'Desfazer' : 'Remover';
            atualizarContador();
        });
    });

    inputCapa.addEventListener('change', () => {
        const arquivo = inputCapa.files[0];
        if (!arquivo) return;
        const url = URL.createObjectURL(arquivo);
        miniaturaCapa.src = url;
        miniaturaCapa.closest('.foto-item').dataset.url = url;
        mostrar(url, 'Nova capa');
    });

    inputNovas.addEventListener('change', () => {
        const escolhidas = Array.from(inputNovas.files).filter(f => f.type.startsWith('image/'));
        const espaco = LIMITE_EXTRAS - extrasMantidas() - novas.length;

        if (escolhidas.length > espaco) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: espaco > 0 ? 'Só cabem mais ' + espaco + ' foto(s).' : 'O produto já tem 5 fotos.', showConfirmButton: false, timer: 2500 });
        }

        novas = novas.concat(escolhidas.slice(0, Math.max(espaco, 0)));
        sincronizar();
        desenharNovas();
    });

    function sincronizar() {
        const transferencia = new DataTransfer();
        novas.forEach(f => transferencia.items.add(f));
        inputNovas.files = transferencia.files;
    }

    function desenharNovas() {
        urlsNovas.forEach(u => URL.revokeObjectURL(u));
        urlsNovas = novas.map(f => URL.createObjectURL(f));
        areaNovas.innerHTML = '';

        urlsNovas.forEach((url, i) => {
            const item = document.createElement('div');
            item.className = 'relative aspect-square rounded-xl border-2 border-dashed border-indigo-300 bg-white p-1 cursor-pointer';
            item.innerHTML =
                '<img src="' + url + '" class="w-full h-full object-contain rounded-lg">' +
                '<span class="absolute bottom-0 inset-x-0 bg-emerald-500 text-white text-[9px] font-bold uppercase text-center rounded-b-lg">Nova</span>' +
                '<button type="button" class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center shadow" title="Remover"><i class="fas fa-times"></i></button>';

            item.addEventListener('click', () => mostrar(url, 'Nova foto'));
            item.querySelector('button').addEventListener('click', (e) => {
                e.stopPropagation();
                novas.splice(i, 1);
                sincronizar();
                desenharNovas();
            });

            areaNovas.appendChild(item);
        });

        atualizarContador();
    }

    atualizarContador();
})();
</script>
@endsection
