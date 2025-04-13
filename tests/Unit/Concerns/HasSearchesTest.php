<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Search;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->test = Refine::make(Product::class);
});

it('is empty by default', function () {
    expect($this->test)
        ->hasSearches()->toBeFalse()
        ->getSearches()->toBeEmpty();
});

it('adds searches', function () {
    expect($this->test)
        ->searches([Search::make('name')])->toBe($this->test)
        ->searches([Search::make('price')])->toBe($this->test)
        ->hasSearches()->toBeTrue()
        ->getSearches()->toHaveCount(2);
});

it('adds searches variadically', function () {
    expect($this->test)
        ->searches(Search::make('name'), Search::make('price'))->toBe($this->test)
        ->hasSearches()->toBeTrue()
        ->getSearches()->toHaveCount(2);
});

it('adds searches collection', function () {
    expect($this->test)
        ->searches(collect([Search::make('name'), Search::make('price')]))->toBe($this->test)
        ->hasSearches()->toBeTrue()
        ->getSearches()->toHaveCount(2);
});

it('has search key', function () {
    expect($this->test)
        ->getSearchKey()->toBe(config('refine.search_key'))
        ->searchKey('test')->toBe($this->test)
        ->getSearchKey()->toBe('test')
        ->getDefaultSearchKey()->toBe(config('refine.search_key'));
});

it('has match key', function () {
    expect($this->test)
        ->getMatchKey()->toBe(config('refine.match_key'))
        ->matchKey('test')->toBe($this->test)
        ->getMatchKey()->toBe('test')
        ->getDefaultMatchKey()->toBe(config('refine.match_key'));
});

it('matches', function () {
    expect($this->test)
        ->isMatching()->toBe(config('refine.match'));

    expect($this->test->matches())->toBe($this->test)
        ->isMatching()->toBeTrue()
        ->isMatchingByDefault()->toBe(config('refine.match'));
});

it('has term', function () {
    expect($this->test)
        ->getTerm()->toBeNull()
        ->term('test')->toBe($this->test)
        ->getTerm()->toBe('test');
});

it('provides searches', function () {
    expect($this->test)
        ->providesSearches()->toBeTrue()
        ->exceptSearches()->toBe($this->test)
        ->providesSearches()->toBeFalse()
        ->onlySearches()->toBe($this->test)
        ->providesSearches()->toBeTrue();
});

it('searches to array', function () {
    expect($this->test)
        ->searches([Search::make('name'), Search::make('price')])->toBe($this->test)
        ->searchesToArray()->toBeEmpty();

    expect($this->test->matches())
        ->searchesToArray()->toHaveCount(2)
        ->each->scoped(fn ($search) => $search
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
    expect($this->test->matches())
        ->searches([Search::make('name')])->toBe($this->test)
        ->searchesToArray()->toHaveCount(1)
        ->exceptSearches()->toBe($this->test)
        ->searchesToArray()->toBeEmpty();
});
