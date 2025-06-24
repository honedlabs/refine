<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Searches\Search;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->refine = Refine::make(User::class);
});

it('is searchable', function () {
    expect($this->refine)
        ->isSearchable()->toBeTrue()
        ->searchable(false)->toBe($this->refine)
        ->isSearchable()->toBeFalse();
});

it('is matchable', function () {
    expect($this->refine)
        ->isMatchable()->toBeFalse()
        ->matchable()->toBe($this->refine)
        ->isMatchable()->toBeTrue();
});

it('can use scout', function () {
    expect($this->refine)
        ->isScout()->toBeFalse()
        ->scout()->toBe($this->refine)
        ->isScout()->toBeTrue();
});

it('adds searches', function () {
    expect($this->refine)
        ->searches([Search::make('name')])->toBe($this->refine)
        ->searches([Search::make('price')])->toBe($this->refine)
        ->getSearches()->toHaveCount(2);
});

it('inserts search', function () {
    expect($this->refine)
        ->search(Search::make('name'))->toBe($this->refine)
        ->getSearches()->toHaveCount(1);
});

it('has search key', function () {
    expect($this->refine)
        ->getSearchKey()->toBe('search')
        ->searchKey('test')->toBe($this->refine)
        ->getSearchKey()->toBe('test');
});

it('has match key', function () {
    expect($this->refine)
        ->getMatchKey()->toBe('match')
        ->matchKey('test')->toBe($this->refine)
        ->getMatchKey()->toBe('test');
});

it('has search placeholder', function () {
    expect($this->refine)
        ->getSearchPlaceholder()->toBeNull()
        ->searchPlaceholder('test')->toBe($this->refine)
        ->getSearchPlaceholder()->toBe('test');
});

it('searches to array', function () {
    expect($this->refine)
        ->searches([Search::make('name'), Search::make('price')])->toBe($this->refine)
        ->searchesToArray()->toBeEmpty();

    expect($this->refine->matchable())
        ->searchesToArray()->toHaveCount(2)
        ->each
        ->scoped(fn ($search) => $search
            ->toHaveKeys([
                'name',
                'label',
                'type',
                'active',
                'meta',
            ])
        );
});

it('hides searches from serialization', function () {
    expect($this->refine->matchable())
        ->searches([Search::make('name')])->toBe($this->refine)
        ->searchesToArray()->toHaveCount(1)
        ->searchable(false)->toBe($this->refine)
        ->searchesToArray()->toBeEmpty();
});
