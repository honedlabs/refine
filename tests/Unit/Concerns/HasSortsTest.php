<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Sort;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->test = Refine::make(Product::class);
});

it('is empty by default', function () {
    expect($this->test)
        ->hasSorts()->toBeFalse()
        ->isSorting()->toBeFalse()
        ->getSorts()->toBeEmpty();
});

it('adds sorts', function () {
    expect($this->test)
        ->sorts([Sort::make('name')])->toBe($this->test)
        ->sorts([Sort::make('price')])->toBe($this->test)
        ->hasSorts()->toBeTrue()
        ->getSorts()->toHaveCount(2);
});

it('adds sorts variadically', function () {
    expect($this->test)
        ->sorts(Sort::make('name'), Sort::make('price'))->toBe($this->test)
        ->hasSorts()->toBeTrue()
        ->getSorts()->toHaveCount(2);
});

it('adds sorts collection', function () {
    expect($this->test)
        ->sorts(collect([Sort::make('name'), Sort::make('price')]))->toBe($this->test)
        ->hasSorts()->toBeTrue()
        ->getSorts()->toHaveCount(2);
});

it('has sort key', function () {
    expect($this->test)
        ->getSortKey()->toBe(config('refine.sort_key'))
        ->sortKey('test')->toBe($this->test)
        ->getSortKey()->toBe('test')
        ->getDefaultSortKey()->toBe(config('refine.sort_key'));
});

it('provides sorts', function () {
    expect($this->test)
        ->providesSorts()->toBeTrue()
        ->exceptSorts()->toBe($this->test)
        ->providesSorts()->toBeFalse()
        ->onlySorts()->toBe($this->test)
        ->providesSorts()->toBeTrue();
});

it('has no default sort', function () {
    expect($this->test)
        ->sorts([Sort::make('name')])->toBe($this->test)
        ->getDefaultSort()->toBeNull();
});

it('has default sort', function () {
    expect($this->test)
        ->sorts([Sort::make('price'), Sort::make('name')->default()])->toBe($this->test)
        ->getDefaultSort()->scoped(fn ($sort) => $sort
        ->not->toBeNull()
        ->getName()->toBe('name')
        );
});

it('sorts to array', function () {
    expect($this->test)
        ->sorts([Sort::make('name'), Sort::make('price')])->toBe($this->test)
        ->sortsToArray()->toHaveCount(2)
        ->each->scoped(fn ($sort) => $sort
        ->toHaveKeys([
            'name',
            'label',
            'type',
            'active',
            'meta',
            'direction',
            'next',
        ])
        );
});

it('hides sorts from serialization', function () {
    expect($this->test)
        ->sorts([Sort::make('name')])->toBe($this->test)
        ->sortsToArray()->toHaveCount(1)
        ->exceptSorts()->toBe($this->test)
        ->sortsToArray()->toBeEmpty();
});
