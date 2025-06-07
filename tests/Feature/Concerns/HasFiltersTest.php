<?php

declare(strict_types=1);

use Honed\Refine\Filter;
use Honed\Refine\Refine;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->test = Refine::make(User::class);
});

it('is empty by default', function () {
    expect($this->test)
        ->isFiltering()->toBeFalse()
        ->getFilters()->toBeEmpty();
});

it('adds filters', function () {
    expect($this->test)
        ->withFilters([Filter::make('name')])->toBe($this->test)
        ->withFilters([Filter::make('price')])->toBe($this->test)
        ->getFilters()->toHaveCount(2);
});

it('adds filters variadically', function () {
    expect($this->test)
        ->withFilters(Filter::make('name'), Filter::make('price'))->toBe($this->test)
        ->getFilters()->toHaveCount(2);
});

it('adds filters collection', function () {
    expect($this->test)
        ->withFilters(collect([Filter::make('name'), Filter::make('price')]))->toBe($this->test)
        ->getFilters()->toHaveCount(2);
});

it('enables and disables filtering', function () {
    expect($this->test)
        // base case
        ->filteringEnabled()->toBeTrue()
        ->filteringDisabled()->toBeFalse()
        // disable
        ->disableFiltering()->toBe($this->test)
        ->filteringEnabled()->toBeFalse()
        ->filteringDisabled()->toBeTrue()
        // enable
        ->enableFiltering()->toBe($this->test)
        ->filteringEnabled()->toBeTrue()
        ->filteringDisabled()->toBeFalse();
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
        ->disableFiltering()->toBe($this->test)
        ->filtersToArray()->toBeEmpty();
});
