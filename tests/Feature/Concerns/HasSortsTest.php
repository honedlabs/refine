<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Sort;
use Workbench\App\Models\User;

beforeEach(function () {
    Refine::useSortKey();
    $this->test = Refine::make(User::class);
});

it('is empty by default', function () {
    expect($this->test)
        ->isSorting()->toBeFalse()
        ->getSorts()->toBeEmpty();
});

it('adds sorts', function () {
    expect($this->test)
        ->withSorts([Sort::make('name')])->toBe($this->test)
        ->withSorts([Sort::make('price')])->toBe($this->test)
        ->getSorts()->toHaveCount(2);
});

it('adds sorts variadically', function () {
    expect($this->test)
        ->withSorts(Sort::make('name'), Sort::make('price'))->toBe($this->test)
        ->getSorts()->toHaveCount(2);
});

it('adds sorts collection', function () {
    expect($this->test)
        ->withSorts(collect([Sort::make('name'), Sort::make('price')]))->toBe($this->test)
        ->getSorts()->toHaveCount(2);
});

it('has sort key', function () {
    expect($this->test)
        ->getSortKey()->toBe('sort')
        ->sortKey('test')->toBe($this->test)
        ->getSortKey()->toBe('test');
});

it('enables and disables sorting', function () {
    expect($this->test)
        // base case
        ->sortingEnabled()->toBeTrue()
        ->sortingDisabled()->toBeFalse()
        // disable
        ->disableSorting()->toBe($this->test)
        ->sortingEnabled()->toBeFalse()
        ->sortingDisabled()->toBeTrue()
        // enable
        ->enableSorting()->toBe($this->test)
        ->sortingEnabled()->toBeTrue()
        ->sortingDisabled()->toBeFalse();
});

it('has no default sort', function () {
    expect($this->test)
        ->withSorts([Sort::make('name')])->toBe($this->test)
        ->getDefaultSort()->toBeNull();
});

it('has default sort', function () {
    expect($this->test)
        ->withSorts([Sort::make('price'), Sort::make('name')->default()])->toBe($this->test)
        ->getDefaultSort()->scoped(fn ($sort) => $sort
        ->not->toBeNull()
        ->getName()->toBe('name')
        );
});

it('sorts to array', function () {
    expect($this->test)
        ->withSorts([Sort::make('name'), Sort::make('price')])->toBe($this->test)
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
        ->withSorts([Sort::make('name')])->toBe($this->test)
        ->sortsToArray()->toHaveCount(1)
        ->disableSorting()->toBe($this->test)
        ->sortsToArray()->toBeEmpty();
});
