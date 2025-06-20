<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Illuminate\Support\Str;
use Workbench\App\Models\AuthUser;
use Workbench\App\Models\Product;
use Workbench\App\Models\User;
use Workbench\App\Refiners\RefineProduct;
use Workbench\App\Refiners\RefineUser;

beforeEach(function () {
    Refine::useNamespace('Workbench\App\Refiners');
    Refine::guessRefinersUsing(fn ($model) => Str::of($model)
        ->afterLast('\\')
        ->prepend('Refine')
        ->prepend('Workbench\App\Refiners\\')
        ->value()
    );
});

afterEach(function () {
    Refine::flushState();
});

it('has refiner from static property', function () {
    expect(AuthUser::refiner())
        ->toBeInstanceOf(RefineUser::class);
});

it('has refiner from attribute', function () {
    expect(User::refiner())
        ->toBeInstanceOf(RefineUser::class);
});

it('has refiner from guessing', function () {
    expect(Product::refiner())
        ->toBeInstanceOf(RefineProduct::class);
});
