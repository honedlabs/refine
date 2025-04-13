<?php

declare(strict_types=1);

use Honed\Refine\Filter;
use Honed\Refine\Refine;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->test = Refine::make(Product::class);
});

it('is empty by default', function () {
    expect($this->test)
        ->hasFilters()->toBeFalse()
        ->getFilters()->toBeEmpty();
});

it('adds filters', function () {
    expect($this->test)
        ->filters([Filter::make('name')])->toBe($this->test)
        ->filters([Filter::make('price')])->toBe($this->test)
        ->hasFilters()->toBeTrue()
        ->getFilters()->toHaveCount(2);
});

it('adds filters variadically', function () {
    expect($this->test)
        ->filters(Filter::make('name'), Filter::make('price'))->toBe($this->test)
        ->hasFilters()->toBeTrue()
        ->getFilters()->toHaveCount(2);
});

it('adds filters collection', function () {
    expect($this->test)
        ->filters(collect([Filter::make('name'), Filter::make('price')]))->toBe($this->test)
        ->hasFilters()->toBeTrue()
        ->getFilters()->toHaveCount(2);
});

it('provides filters', function () {
    expect($this->test)
        ->providesFilters()->toBeTrue()
        ->exceptFilters()->toBe($this->test)
        ->providesFilters()->toBeFalse()
        ->onlyFilters()->toBe($this->test)
        ->providesFilters()->toBeTrue();
});

it('filters to array', function () {
    expect($this->test)
        ->filters([Filter::make('name'), Filter::make('price')])->toBe($this->test)
        ->filtersToArray()->toHaveCount(2)
        ->each->scoped(fn ($filter) => $filter
        ->toHaveKeys([
            'name',
            'label',
            'type',
            'active',
            'meta',
            'value',
            'options',
        ])
        );
});

it('hides filters from serialization', function () {
    expect($this->test)
        ->filters([Filter::make('name')])->toBe($this->test)
        ->filtersToArray()->toHaveCount(1)
        ->exceptFilters()->toBe($this->test)
        ->filtersToArray()->toBeEmpty();
});
