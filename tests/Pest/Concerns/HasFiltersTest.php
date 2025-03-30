<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Filter;
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
        ->withFilters([Filter::make('name')])->toBe($this->test)
        ->withFilters([Filter::make('price')])->toBe($this->test)
        ->hasFilters()->toBeTrue()
        ->getFilters()->toHaveCount(2);
});

it('adds filters variadically', function () {
    expect($this->test)
        ->withFilters(Filter::make('name'), Filter::make('price'))->toBe($this->test)
        ->hasFilters()->toBeTrue()
        ->getFilters()->toHaveCount(2);
});

it('adds filters collection', function () {
    expect($this->test)
        ->withFilters(collect([Filter::make('name'), Filter::make('price')]))->toBe($this->test)
        ->hasFilters()->toBeTrue()
        ->getFilters()->toHaveCount(2);
});

it('without filters', function () {
    expect($this->test)
        ->isWithoutFilters()->toBeFalse()
        ->withoutFilters()->toBe($this->test)
        ->isWithoutFilters()->toBeTrue();
});

it('filters to array', function () {
    expect($this->test)
        ->withFilters([Filter::make('name'), Filter::make('price')])->toBe($this->test)
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
        ->withFilters([Filter::make('name')])->toBe($this->test)
        ->filtersToArray()->toHaveCount(1)
        ->withoutFilters()->toBe($this->test)
        ->filtersToArray()->toBeEmpty();
});
