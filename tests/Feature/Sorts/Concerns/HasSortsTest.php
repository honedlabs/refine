<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Sorts\Sort;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->test = Refine::make(User::class);
});

it('is sortable', function () {
    expect($this->test)
        ->isSortable()->toBeTrue()
        ->sortable(false)->toBe($this->test)
        ->isSortable()->toBeFalse();
});

it('adds sorts', function () {
    expect($this->test)
        ->sorts([Sort::make('name')])->toBe($this->test)
        ->sorts([Sort::make('price')])->toBe($this->test)
        ->getSorts()->toHaveCount(2);
});

it('has sort key', function () {
    expect($this->test)
        ->getSortKey()->toBe('sort')
        ->sortKey('test')->toBe($this->test)
        ->getSortKey()->toBe('test');
});

it('has no default sort', function () {
    expect($this->test)
        ->sorts([Sort::make('name')])->toBe($this->test)
        ->getDefaultSort()->toBeNull();
});

it('has default sort', function () {
    expect($this->test)
        ->sorts([Sort::make('price'), Sort::make('name')->default()])->toBe($this->test)
        ->getDefaultSort()
        ->scoped(fn ($sort) => $sort
            ->not->toBeNull()
            ->getName()->toBe('name')
        );
});

it('sorts to array', function () {
    expect($this->test)
        ->sorts([Sort::make('name'), Sort::make('price')])->toBe($this->test)
        ->sortsToArray()->toHaveCount(2)
        ->each
        ->scoped(fn ($sort) => $sort
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
        ->sortable(false)->toBe($this->test)
        ->sortsToArray()->toBeEmpty();
});
