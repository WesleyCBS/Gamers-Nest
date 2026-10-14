<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    private function getSettingsPath() { return storage_path('app/settings.json'); }

    private function getSettings() 
    {
        if (!file_exists($this->getSettingsPath())) {
            return [
                'logo' => null, 
                'banners' => [], 
                'pagamento_pix' => true, 
                'pagamento_credito' => true, 
                'pagamento_debito' => true
            ];
        }
        $settings = json_decode(file_get_contents($this->getSettingsPath()), true);
        if (!isset($settings['banners'])) {
            $settings['banners'] = [];
        }
        return $settings;
    }

    public function iniciar(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $carrinho = session()->get('carrinho', []);

        if(isset($carrinho[$id])) {
            $carrinho[$id]['quantidade']++;
        } else {
            $carrinho[$id] = [
                "nome" => $produto->nome,
                "quantidade" => 1,
                "preço" => $produto->preço,
                "imagem" => $produto->imagem
            ];
        }

        session()->put('carrinho', $carrinho);
        return redirect()->route('carrinho.index');
    }

    public function index()
    {
        if (!session()->has('carrinho') || count(session('carrinho')) == 0) {
            return redirect()->route('carrinho.index')->with('error', 'Seu carrinho está vazio.');
        }
        return redirect()->route('carrinho.index');
    }

    public function configuracoesForm()
    {
        $config = $this->getSettings();
        return view('produtos.configuracoes', ['configuracoes' => (object) $config]);
    }

    public function salvarConfiguracoes(Request $request)
    {
        $config = $this->getSettings();
        
        // Tratamento da Logo
        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048']);
            if (!empty($config['logo']) && Storage::disk('public')->exists($config['logo'])) {
                Storage::disk('public')->delete($config['logo']);
            }
            $config['logo'] = $request->file('logo')->store('config', 'public');
        }

        // Tratamento dos Banners do Carrossel
        $banners = $config['banners'] ?? [];
        if ($request->hasFile('banners')) {
            $request->validate(['banners.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096']);
            foreach ($request->file('banners') as $bannerFile) {
                $path = $bannerFile->store('banners', 'public');
                $banners[] = $path;
            }
        }
        $config['banners'] = $banners;

        // Métodos de Pagamento
        $config['pagamento_pix'] = $request->has('pagamento_pix');
        $config['pagamento_credito'] = $request->has('pagamento_credito');
        $config['pagamento_debito'] = $request->has('pagamento_debito');

        file_put_contents($this->getSettingsPath(), json_encode($config, JSON_PRETTY_PRINT));
        return redirect()->back()->with('success', 'Configurações salvas com sucesso!');
    }

    public function removerBanner($index)
    {
        $config = $this->getSettings();
        if (isset($config['banners'][$index])) {
            if (Storage::disk('public')->exists($config['banners'][$index])) {
                Storage::disk('public')->delete($config['banners'][$index]);
            }
            unset($config['banners'][$index]);
            $config['banners'] = array_values($config['banners']); // Reindexa o array
            file_put_contents($this->getSettingsPath(), json_encode($config, JSON_PRETTY_PRINT));
        }
        return redirect()->back()->with('success', 'Banner removido com sucesso!');
    }

    public function enderecoForm()
    {
        return redirect()->route('carrinho.index');
    }

    public function salvarEndereco(Request $request)
    {
        $dadosEndereco = $request->validate([
            'cep' => 'required|string|max:9',
            'logradouro' => 'required|string|max:255',
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
            return redirect()->route('carrinho.index');
        }
        
        $endereco = session('checkout_endereco');
        $config = $this->getSettings();
        $carrinho = session('carrinho', []);

        return view('produtos.pagamento', compact('endereco', 'config', 'carrinho'));
    }

    public function finalizarCompra(Request $request)
    {
        $request->validate(['metodo_pagamento' => 'required|string']);
        session()->forget(['checkout_endereco', 'carrinho']);
        return response()->json(['success' => true]);
    }
}