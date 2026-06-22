<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roupa;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    private function getSettingsPath() 
    {
        return storage_path('app/settings.json');
    }

    private function getSettings() 
    {
        if (!file_exists($this->getSettingsPath())) {
            return [
                'logo' => null, 
                'pagamento_pix' => true, 
                'pagamento_credito' => true, 
                'pagamento_debito' => true
            ];
        }
        return json_decode(file_get_contents($this->getSettingsPath()), true);
    }

    public function index()
    {
        if (!session()->has('carrinho') || count(session('carrinho')) == 0) {
            return redirect()->route('carrinho.index')->with('error', 'Seu carrinho está vazio.');
        }

        return redirect()->route('checkout.endereco');
    }

    public function configuracoesForm()
    {
        $config = $this->getSettings();
        return view('roupas.configuracoes', ['configuracoes' => (object) $config]);
    }

    public function salvarConfiguracoes(Request $request)
    {
        $config = $this->getSettings();

        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048']);

            if (!empty($config['logo']) && Storage::disk('public')->exists($config['logo'])) {
                Storage::disk('public')->delete($config['logo']);
            }
            
            $config['logo'] = $request->file('logo')->store('config', 'public');
        }

        $config['pagamento_pix'] = $request->has('pagamento_pix');
        $config['pagamento_credito'] = $request->has('pagamento_credito');
        $config['pagamento_debito'] = $request->has('pagamento_debito');

        file_put_contents($this->getSettingsPath(), json_encode($config, JSON_PRETTY_PRINT));

        return redirect()->back()->with('success', 'Configurações salvas com sucesso!');
    }

    public function iniciar(Request $request, $id)
    {
        $roupa = Roupa::findOrFail($id);
        session(['checkout_produto_id' => $roupa->id]);
        return redirect()->route('checkout.endereco');
    }

    public function enderecoForm()
    {
        $roupa = null;
        if (session()->has('checkout_produto_id')) {
            $roupa = Roupa::find(session('checkout_produto_id'));
        }
        return view('roupas.endereco', compact('roupa'));
    }

    public function salvarEndereco(Request $request)
    {
        $dadosEndereco = $request->validate([
            'cep' => 'required|string|max:9',
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
        ]);
        session(['checkout_endereco' => $dadosEndereco]);
        return redirect()->route('checkout.pagamento');
    }

    public function pagamentoForm()
    {
        if (!session()->has('checkout_endereco')) {
            return redirect()->route('checkout.endereco');
        }
        
        $endereco = session('checkout_endereco');
        $config = $this->getSettings();

        return view('roupas.pagamento', compact('endereco', 'config'));
    }

    public function finalizarCompra(Request $request)
    {
        $request->validate(['metodo_pagamento' => 'required|string']);
        session()->forget(['checkout_produto_id', 'checkout_endereco', 'carrinho']);
        return response()->json(['success' => true]);
    }
}