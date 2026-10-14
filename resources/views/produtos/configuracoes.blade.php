@extends('layouts.app')

@section('slot')
<div class="max-w-4xl mx-auto py-10 px-4">
    <div class="bg-white shadow-xl rounded-3xl p-8 border border-gray-100">
        <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter mb-8 flex items-center gap-2">
            <i class="fas fa-cog text-indigo-600"></i> Configurações do Sistema
        </h2>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-bold text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form id="form-configuracoes" action="{{ route('admin.configuracoes.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Logo da Loja</label>
                <input type="file" name="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 file:cursor-pointer file:transition-all file:hover:bg-indigo-100 file:hover:shadow-sm">
                @if(isset($configuracoes->logo) && $configuracoes->logo)
                    <img src="{{ Storage::url($configuracoes->logo) }}" class="mt-4 h-16 w-auto">
                @endif
            </div>

            <!-- Seção de Banners do Carrossel -->
            <div class="border-t border-gray-100 pt-6">
                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Banners do Carrossel</label>
                <p class="text-xs text-gray-400 mb-4">Adicione múltiplos banners para rotacionar na página inicial.</p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    @if(!empty($configuracoes->banners ?? []))
                        @foreach($configuracoes->banners as $index => $banner)
                            <div class="relative group rounded-2xl overflow-hidden border border-gray-200 h-28 bg-gray-50 flex items-center justify-center">
                                <img src="{{ Storage::url($banner) }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="submit" form="form-delete-banner-{{ $index }}" class="bg-red-500 hover:bg-red-600 text-white p-2.5 rounded-full transition shadow-lg cursor-pointer">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-xs text-gray-400 col-span-full">Nenhum banner cadastrado no momento.</p>
                    @endif
                </div>

                <input type="file" name="banners[]" multiple accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 file:cursor-pointer file:transition-all file:hover:bg-indigo-100 file:hover:shadow-sm">
            </div>

            <div class="border-t border-gray-100 pt-6">
                <label class="block text-xs font-bold uppercase text-gray-500 mb-4">Métodos de Pagamento</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                        <input type="checkbox" name="pagamento_pix" value="1" 
                            {{ (isset($configuracoes->pagamento_pix) && $configuracoes->pagamento_pix) ? 'checked' : '' }} 
                            class="w-5 h-5 text-indigo-600 rounded">
                        <span class="font-bold text-sm text-gray-700">PIX</span>
                    </label>

                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                        <input type="checkbox" name="pagamento_credito" value="1" 
                            {{ (isset($configuracoes->pagamento_credito) && $configuracoes->pagamento_credito) ? 'checked' : '' }} 
                            class="w-5 h-5 text-indigo-600 rounded">
                        <span class="font-bold text-sm text-gray-700">Crédito</span>
                    </label>

                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                        <input type="checkbox" name="pagamento_debito" value="1" 
                            {{ (isset($configuracoes->pagamento_debito) && $configuracoes->pagamento_debito) ? 'checked' : '' }} 
                            class="w-5 h-5 text-indigo-600 rounded">
                        <span class="font-bold text-sm text-gray-700">Boleto</span>
                    </label>

                </div>
            </div>

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold uppercase text-xs tracking-widest px-8 py-3 rounded-xl shadow-md transition-all">
                Salvar Configurações
            </button>
        </form>

        <!-- Formulários de remoção de banners (colocados fora do formulário principal para evitar aninhamento) -->
        @if(!empty($configuracoes->banners ?? []))
            @foreach($configuracoes->banners as $index => $banner)
                <form id="form-delete-banner-{{ $index }}" action="{{ route('admin.banner.remover', $index) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @endif

    </div>
</div>
@endsection