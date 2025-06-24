<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Sorts\Sort;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->refine = Refine::make(User::class);
});

it('is sortable', function () {
    expect($this->refine)
        ->isSortable()->toBeTrue()
        ->sortable(false)->toBe($this->refine)
        ->isSortable()->toBeFalse();
});

it('adds sorts', function () {
    expect($this->refine)
        ->sorts([Sort::make('name')])->toBe($this->refine)
        ->sorts([Sort::make('price')])->toBe($this->refine)
        ->getSorts()->toHaveCount(2);
});

it('inserts sort', function () {
    expect($this->refine)
        ->sort(Sort::make('name'))->toBe($this->refine)
        ->getSorts()->toHaveCount(1);
});

it('has sort key', function () {
    expect($this->refine)
        ->getSortKey()->toBe('sort')
        ->sortKey('test')->toBe($this->refine)
        ->getSortKey()->toBe('test');
});

it('has no default sort', function () {
    expect($this->refine)
        ->sorts([Sort::make('name')])->toBe($this->refine)
        ->getDefaultSort()->toBeNull();
});

it('has default sort', function () {
    expect($this->refine)
        ->sorts([Sort::make('price'), Sort::make('name')->default()])->toBe($this->refine)
        ->getDefaultSort()
        ->scoped(fn ($sort) => $sort
            ->not->toBeNull()
            ->getName()->toBe('name')
        );
});

it('sorts to array', function () {
    expect($this->refine)
        ->sorts([Sort::make('name'), Sort::make('price')])->toBe($this->refine)
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
    expect($this->refine)
        ->sorts([Sort::make('name')])->toBe($this->refine)
        ->sortsToArray()->toHaveCount(1)
        ->sortable(false)->toBe($this->refine)
        ->sortsToArray()->toBeEmpty();
});
