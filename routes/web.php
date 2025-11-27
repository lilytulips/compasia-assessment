<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('products');
});

Route::get('/products', function () {
    return Inertia::render('ProductsView');
})->name('products');
