<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Workbench\App\Models\Product;

Route::get('/', function () {
    return view('welcome');
});

Route::get('products/{product}', fn (Product $product) => $product)
    ->name('products.show');
