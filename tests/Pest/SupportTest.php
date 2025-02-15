<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Sorts\Sort;
use Honed\Refine\Filters\Filter;
use Honed\Refine\Searches\Search;
use Honed\Refine\Tests\Stubs\Product;

it('can set the sorts key', function () {
    expect(Refine::make(Product::class))
        ->getSortsKey()->toBe(config('refine.keys.sorts'))
        ->sortsKey('test')->toBeInstanceOf(Refine::class)
        ->getSortsKey()->toBe('test');
});

it('can set the search key', function () {
    expect(Refine::make(Product::class))
        ->getSearchesKey()->toBe(config('refine.keys.searches'))
        ->searchesKey('test')->toBeInstanceOf(Refine::class)
        ->getSearchesKey()->toBe('test');
});

it('can set the matches key', function () {
    expect(Refine::make(Product::class))
        ->getMatchesKey()->toBe(config('refine.keys.matches'))
        ->matchesKey('test')->toBeInstanceOf(Refine::class)
        ->getMatchesKey()->toBe('test');
});

it('can set as matching', function () {
    expect(Refine::make(Product::class))
        ->canMatch()->toBe(config('refine.matches'))
        ->matches()->toBeInstanceOf(Refine::class)
        ->canMatch()->toBeTrue();
});

it('has sorts method', function () {
    expect(Refine::make(Product::class))
        ->sorts([Sort::make('name', 'A-Z')])->toBeInstanceOf(Refine::class)
        ->getSorts()->toHaveCount(1);
});

it('has filters method', function () {
    expect(Refine::make(Product::class))
        ->filters([Filter::make('name')])->toBeInstanceOf(Refine::class)
        ->getFilters()->toHaveCount(1);
});

it('has searches method', function () {
    expect(Refine::make(Product::class))
        ->searches([Search::make('name')])->toBeInstanceOf(Refine::class)
        ->getSearches()->toHaveCount(1);
});





