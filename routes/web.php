<?php

use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CarrinhoController; 
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

Route::get('/', [ProdutoController::class, 'index'])->name('produtos.index');

Route::get('/carrinho', [CarrinhoController::class, 'index'])->name('carrinho.index');
Route::post('/carrinho/adicionar/{id}', [CarrinhoController::class, 'adicionar'])->name('carrinho.adicionar');
Route::patch('/carrinho/atualizar/{id}', [CarrinhoController::class, 'atualizar'])->name('carrinho.atualizar');
Route::delete('/carrinho/remover/{id}', [CarrinhoController::class, 'remover'])->name('carrinho.remover');

Route::get('/categoria/{slug}', [ProdutoController::class, 'porCategoria'])->name('produtos.categoria');

Route::get('/produtos/{id}', [ProdutoController::class, 'show'])
    ->name('produtos.show')
    ->where('id', '[0-9]+');

Route::middleware('auth')->group(function () {
    
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    
    Route::post('/checkout/iniciar/{id}', [CheckoutController::class, 'iniciar'])->name('checkout.iniciar');
    Route::get('/checkout/endereco', [CheckoutController::class, 'enderecoForm'])->name('checkout.endereco');
    Route::post('/checkout/endereco', [CheckoutController::class, 'salvarEndereco'])->name('checkout.salvar_endereco');
    Route::get('/checkout/pagamento', [CheckoutController::class, 'pagamentoForm'])->name('checkout.pagamento');
    Route::post('/checkout/finalizar', [CheckoutController::class, 'finalizarCompra'])->name('checkout.finalizar');

    Route::post('/produtos/{id}/favorito', [ProdutoController::class, 'favorito'])->name('produtos.favorito');
    Route::get('/favoritos', [ProdutoController::class, 'favoritos'])->name('produtos.favoritos');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->group(function () {
        
        Route::get('/dashboard', function () {
            return redirect()->route('produtos.index');
        })->name('dashboard');

        Route::get('/produtos/configuracoes', [CheckoutController::class, 'configuracoesForm'])->name('admin.configuracoes');
        Route::put('/produtos/configuracoes', [CheckoutController::class, 'salvarConfiguracoes'])->name('admin.configuracoes.update');

        Route::get('/produtos/create', [ProdutoController::class, 'create'])->name('produtos.create');
        Route::post('/produtos', [ProdutoController::class, 'store'])->name('produtos.store');
        Route::get('/produtos/{id}/edit', [ProdutoController::class, 'edit'])->name('produtos.edit');
        Route::put('/produtos/{id}', [ProdutoController::class, 'update'])->name('produtos.update');
        Route::delete('/produtos/{id}', [ProdutoController::class, 'destroy'])->name('produtos.destroy');

        Route::get('/categorias/create', [CategoriaController::class, 'create'])->name('categorias.create');
        Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
        Route::get('/categorias/{id}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit');
        Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
    });
});

Route::get('/storage/{path}', function ($path) {
    $path = str_replace('storage/', '', $path);
    $fullPath = storage_path('app/public/' . $path);
    
    if (!File::exists($fullPath)) {
        $fullPath = storage_path('app/public/products/' . $path);
    }
    
    if (!File::exists($fullPath)) {
        abort(404);
    }
    
    return Response::make(File::get($fullPath), 200)
        ->header("Content-Type", File::mimeType($fullPath));
})->where('path', '.*');

require __DIR__.'/auth.php';