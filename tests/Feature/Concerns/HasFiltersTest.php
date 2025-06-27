<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;
use Honed\Refine\Refine;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->refine = Refine::make(User::class);
});

it('is filterable', function () {
    expect($this->refine)
        ->isFilterable()->toBeTrue()
        ->isNotFilterable()->toBeFalse()
        ->notFilterable()->toBe($this->refine)
        ->isNotFilterable()->toBeTrue()
        ->filterable()->toBe($this->refine)
        ->isFilterable()->toBeTrue();
});

it('adds filters', function () {
    expect($this->refine)
        ->filters([Filter::make('name')])->toBe($this->refine)
        ->filters([Filter::make('price')])->toBe($this->refine)
        ->getFilters()->toHaveCount(2);
});

it('inserts filter', function () {
    expect($this->refine)
        ->filter(Filter::make('name'))->toBe($this->refine)
        ->getFilters()->toHaveCount(1);
});

it('checks if it is filtering', function () {
    expect($this->refine)
        ->filter(Filter::make('name')->active(true))
        ->isFiltering()->toBeTrue()
        ->isNotFiltering()->toBeFalse();
});

it('checks if it is not filtering', function () {
    expect($this->refine)
        ->filter(Filter::make('name')->active(false))
        ->isNotFiltering()->toBeTrue()
        ->isFiltering()->toBeFalse();
});

it('gets active filters', function () {
    expect($this->refine)
        ->filters([
            Filter::make('name')->active(true),
            Filter::make('price')->active(false),
        ])
        ->toBe($this->refine)
        ->getActiveFilters()->toHaveCount(1);
});

it('filters to array', function () {
    expect($this->refine)
        ->filters([Filter::make('name'), Filter::make('price')])->toBe($this->refine)
        ->filtersToArray()->toHaveCount(2)
        ->each
        ->scoped(fn ($filter) => $filter
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
    expect($this->refine)
        ->filters([Filter::make('name')])->toBe($this->refine)
        ->filtersToArray()->toHaveCount(1)
        ->filterable(false)->toBe($this->refine)
        ->filtersToArray()->toBeEmpty();
});
