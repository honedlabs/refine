<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Illuminate\Support\Str;
use Workbench\App\Models\AuthUser;
use Workbench\App\Models\Product;
use Workbench\App\Models\User;
use Workbench\App\Refiners\ProductRefiner;
use Workbench\App\Refiners\UserRefiner;

beforeEach(function () {
    Refine::useNamespace('Workbench\App\Refiners');
    Refine::guessRefinersUsing(fn ($model) => Str::of($model)
        ->afterLast('\\')
        ->append('Refiner')
        ->prepend('Workbench\App\Refiners\\')
        ->value()
    );
});

afterEach(function () {
    Refine::flushState();
});

it('has refiner from static property', function () {
    expect(AuthUser::refiner())
        ->toBeInstanceOf(UserRefiner::class);
});

it('has refiner from attribute', function () {
    expect(User::refiner())
        ->toBeInstanceOf(UserRefiner::class);
});

it('has refiner from guessing', function () {
    expect(Product::refiner())
        ->toBeInstanceOf(ProductRefiner::class);
});
