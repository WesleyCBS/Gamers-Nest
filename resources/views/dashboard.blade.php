<?php

use App\Http\Controllers\RoupaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoriaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rota Principal (O link http://127.0.0.1:8000 que você quer)
|--------------------------------------------------------------------------
*/
Route::get('/', [RoupaController::class, 'index'])->name('roupas.index');

Route::get('/categoria/{slug}', [RoupaController::class, 'porCategoria'])->name('roupas.categoria');
Route::get('/roupas/{id}', [RoupaController::class, 'show'])->name('roupas.show')->where('id', '[0-9]+');

/*
|--------------------------------------------------------------------------
| Rotas Protegidas
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    Route::post('/roupas/{id}/favorito', [RoupaController::class, 'favorito'])->name('roupas.favorito');
    Route::get('/favoritos', [RoupaController::class, 'favoritos'])->name('roupas.favoritos');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->group(function () {
        
        // --- A MUDANÇA ESTÁ AQUI ---
        // Em vez de retornar a 'view(dashboard)', nós redirecionamos para a index
        // Assim, o sistema de login não dá erro e você cai na tela que deseja.
        Route::get('/dashboard', function () {
            return redirect()->route('roupas.index');
        })->name('dashboard');

        // CRUD PRODUTOS
        Route::get('/roupas/create', [RoupaController::class, 'create'])->name('roupas.create');
        Route::post('/roupas', [RoupaController::class, 'store'])->name('roupas.store');
        Route::get('/roupas/{id}/edit', [RoupaController::class, 'edit'])->name('roupas.edit');
        Route::put('/roupas/{id}', [RoupaController::class, 'update'])->name('roupas.update');
        Route::delete('/roupas/{id}', [RoupaController::class, 'destroy'])->name('roupas.destroy');

        // CRUD CATEGORIAS
        Route::get('/categorias/create', [CategoriaController::class, 'create'])->name('categorias.create');
        Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
        Route::get('/categorias/{id}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit');
        Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
    });
});

require __DIR__.'/auth.php';