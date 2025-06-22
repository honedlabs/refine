<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Searches\Search;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->test = Refine::make(User::class);
});

it('is searchable', function () {
    expect($this->test)
        ->isSearchable()->toBeTrue()
        ->searchable(false)->toBe($this->test)
        ->isSearchable()->toBeFalse();
});

it('is matchable', function () {
    expect($this->test)
        ->isMatchable()->toBeFalse()
        ->matchable()->toBe($this->test)
        ->isMatchable()->toBeTrue();
});

it('can use scout', function () {
    expect($this->test)
        ->isScout()->toBeFalse()
        ->scout()->toBe($this->test)
        ->isScout()->toBeTrue();
});

it('adds searches', function () {
    expect($this->test)
        ->searches([Search::make('name')])->toBe($this->test)
        ->searches([Search::make('price')])->toBe($this->test)
        ->getSearches()->toHaveCount(2);
});

it('has search key', function () {
    expect($this->test)
        ->getSearchKey()->toBe('search')
        ->searchKey('test')->toBe($this->test)
        ->getSearchKey()->toBe('test');
});

it('has match key', function () {
    expect($this->test)
        ->getMatchKey()->toBe('match')
        ->matchKey('test')->toBe($this->test)
        ->getMatchKey()->toBe('test');
});

it('has search placeholder', function () {
    expect($this->test)
        ->getSearchPlaceholder()->toBeNull()
        ->searchPlaceholder('test')->toBe($this->test)
        ->getSearchPlaceholder()->toBe('test');
});

it('searches to array', function () {
    expect($this->test)
        ->searches([Search::make('name'), Search::make('price')])->toBe($this->test)
        ->searchesToArray()->toBeEmpty();

    expect($this->test->matchable())
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
    expect($this->test->matchable())
        ->searches([Search::make('name')])->toBe($this->test)
        ->searchesToArray()->toHaveCount(1)
        ->searchable(false)->toBe($this->test)
        ->searchesToArray()->toBeEmpty();
});
