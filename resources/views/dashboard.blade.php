<?php

use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoriaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProdutoController::class, 'index'])->name('produtos.index');

Route::get('/categoria/{slug}', [ProdutoController::class, 'porCategoria'])->name('produtos.categoria');
Route::get('/produtos/{id}', [ProdutoController::class, 'show'])->name('produtos.show')->where('id', '[0-9]+');

Route::middleware('auth')->group(function () {
    
    Route::post('/produtos/{id}/favorito', [ProdutoController::class, 'favorito'])->name('produtos.favorito');
    Route::get('/favoritos', [ProdutoController::class, 'favoritos'])->name('produtos.favoritos');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->group(function () {
        
        Route::get('/dashboard', function () {
            return redirect()->route('produtos.index');
        })->name('dashboard');

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

require __DIR__.'/auth.php';