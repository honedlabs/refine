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
        ->filterable(false)->toBe($this->refine)
        ->isFilterable()->toBeFalse();
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
