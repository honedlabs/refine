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
        ->isNotSearchable()->toBeFalse()
        ->notSearchable()->toBe($this->refine)
        ->isNotSearchable()->toBeTrue()
        ->searchable()->toBe($this->refine)
        ->isSearchable()->toBeTrue();
});

it('is matchable', function () {
    expect($this->refine)
        ->isNotMatchable()->toBeTrue()
        ->isMatchable()->toBeFalse()
        ->matchable()->toBe($this->refine)
        ->isMatchable()->toBeTrue()
        ->notMatchable()->toBe($this->refine)
        ->isNotMatchable()->toBeTrue();
});

it('can use scout', function () {
    expect($this->refine)
        ->isNotScout()->toBeTrue()
        ->isScout()->toBeFalse()
        ->scout()->toBe($this->refine)
        ->isScout()->toBeTrue()
        ->notScout()->toBe($this->refine)
        ->isNotScout()->toBeTrue();
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

it('checks if it is searching', function () {
    expect($this->refine)
        ->setSearchTerm('test')->toBeNull()
        ->isSearching()->toBeTrue()
        ->isNotSearching()->toBeFalse();
});

it('checks if it is not searching', function () {
    expect($this->refine)
        ->isNotSearching()->toBeTrue()
        ->isSearching()->toBeFalse();
});

it('gets active searches', function () {
    expect($this->refine)
        ->searches([
            Search::make('name')->active(true),
            Search::make('price')->active(false),
        ])
        ->toBe($this->refine)
        ->getActiveSearches()->toHaveCount(1);
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
