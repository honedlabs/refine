<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;
use Honed\Refine\Refine;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->test = Refine::make(User::class);
});

it('is filterable', function () {
    expect($this->test)
        ->filterable()->toBe($this->test)
        ->isFilterable()->toBeTrue()
        ->isNotFilterable()->toBeFalse()
        ->notFilterable()->toBe($this->test)
        ->isFilterable()->toBeFalse()
        ->isNotFilterable()->toBeTrue();
});

it('adds filters', function () {
    expect($this->test)
        ->filters([Filter::make('name')])->toBe($this->test)
        ->filters([Filter::make('price')])->toBe($this->test)
        ->getFilters()->toHaveCount(2);
});

it('filters to array', function () {
    expect($this->test)
        ->filters([Filter::make('name'), Filter::make('price')])->toBe($this->test)
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
    expect($this->test)
        ->filters([Filter::make('name')])->toBe($this->test)
        ->filtersToArray()->toHaveCount(1)
        ->notFilterable()->toBe($this->test)
        ->filtersToArray()->toBeEmpty();
});
