<?php

use App\Http\Controllers\RoupaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CarrinhoController; 
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', [RoupaController::class, 'index'])->name('roupas.index');

Route::get('/carrinho', [CarrinhoController::class, 'index'])->name('carrinho.index');
Route::post('/carrinho/adicionar/{id}', [CarrinhoController::class, 'adicionar'])->name('carrinho.adicionar');
Route::delete('/carrinho/remover/{id}', [CarrinhoController::class, 'remover'])->name('carrinho.remover');

// Rota de Filtro por Categoria (Slug)
Route::get('/categoria/{slug}', [RoupaController::class, 'porCategoria'])->name('roupas.categoria');

// A rota 'show' com 'where' numérico
Route::get('/roupas/{id}', [RoupaController::class, 'show'])
    ->name('roupas.show')
    ->where('id', '[0-9]+');


/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Precisa estar Logado)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    // --- FLUXO DE COMPRA ---
    Route::post('/checkout/iniciar/{id}', [CheckoutController::class, 'iniciar'])->name('checkout.iniciar');
    Route::get('/checkout/endereco', [CheckoutController::class, 'enderecoForm'])->name('checkout.endereco');
    Route::post('/checkout/endereco', [CheckoutController::class, 'salvarEndereco'])->name('checkout.salvar_endereco');
    Route::get('/checkout/pagamento', [CheckoutController::class, 'pagamentoForm'])->name('checkout.pagamento');
    Route::post('/checkout/finalizar', [CheckoutController::class, 'finalizarCompra'])->name('checkout.finalizar');

    // Sistema de Favoritos
    Route::post('/roupas/{id}/favorito', [RoupaController::class, 'favorito'])->name('roupas.favorito');
    Route::get('/favoritos', [RoupaController::class, 'favoritos'])->name('roupas.favoritos');

    // Perfil do Usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Painel Administrativo (Apenas para Admins)
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->group(function () {
        
        Route::get('/dashboard', function () {
            return redirect()->route('roupas.index');
        })->name('dashboard');

        // Configurações do Admin
        Route::get('/roupas/configuracoes', [CheckoutController::class, 'configuracoesForm'])->name('admin.configuracoes');
        Route::put('/roupas/configuracoes', [CheckoutController::class, 'salvarConfiguracoes'])->name('admin.configuracoes.update');

        // CRUD DE PRODUTOS
        Route::get('/roupas/create', [RoupaController::class, 'create'])->name('roupas.create');
        Route::post('/roupas', [RoupaController::class, 'store'])->name('roupas.store');
        Route::get('/roupas/{id}/edit', [RoupaController::class, 'edit'])->name('roupas.edit');
        Route::put('/roupas/{id}', [RoupaController::class, 'update'])->name('roupas.update');
        Route::delete('/roupas/{id}', [RoupaController::class, 'destroy'])->name('roupas.destroy');

        // CRUD DE CATEGORIAS
        Route::get('/categorias/create', [CategoriaController::class, 'create'])->name('categorias.create');
        Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
        Route::get('/categorias/{id}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit');
        Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Rota de Ajuste para Imagens no Windows
|--------------------------------------------------------------------------
*/
Route::get('/storage/products/{filename}', function ($filename) {
    $path = storage_path('app/public/products/' . $filename);
    if (!File::exists($path)) { abort(404); }
    $file = File::get($path);
    $type = File::mimeType($path);
    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);
    return $response;
});

require __DIR__.'/auth.php';