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

        <form action="{{ route('admin.configuracoes.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Logo da Loja</label>
                <input type="file" name="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                @if(isset($configuracoes->logo) && $configuracoes->logo)
                    <img src="{{ Storage::url($configuracoes->logo) }}" class="mt-4 h-16 w-auto">
                @endif
            </div>

            <div>
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
    </div>
</div>
@endsection