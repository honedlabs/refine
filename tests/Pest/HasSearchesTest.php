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
        ->withSearches([Search::make('name')])->toBe($this->test)
        ->withSearches([Search::make('price')])->toBe($this->test)
        ->hasSearches()->toBeTrue()
        ->getSearches()->toHaveCount(2);
});

it('adds searches variadically', function () {
    expect($this->test)
        ->withSearches(Search::make('name'), Search::make('price'))->toBe($this->test)
        ->hasSearches()->toBeTrue()
        ->getSearches()->toHaveCount(2);
});

it('adds searches collection', function () {
    expect($this->test)
        ->withSearches(collect([Search::make('name'), Search::make('price')]))->toBe($this->test)
        ->hasSearches()->toBeTrue()
        ->getSearches()->toHaveCount(2);
});

it('has searches key', function () {
    expect($this->test)
        ->getSearchesKey()->toBe(config('refine.searches_key'))
        ->searchesKey('test')->toBe($this->test)
        ->getSearchesKey()->toBe('test')
        ->fallbackSearchesKey()->toBe(config('refine.searches_key'));
});

it('has matches key', function () {
    expect($this->test)
        ->getMatchesKey()->toBe(config('refine.matches_key'))
        ->matchesKey('test')->toBe($this->test)
        ->getMatchesKey()->toBe('test')
        ->fallbackMatchesKey()->toBe(config('refine.matches_key'));
});

it('matches', function () {
    expect($this->test)
        ->matches()->toBe(config('refine.match'));

    expect($this->test->match())->toBe($this->test)
        ->matches()->toBeTrue()
        ->fallbackMatches()->toBe(config('refine.match'));
});

it('is searching', function () {
    expect($this->test)
        ->isSearching()->toBeTrue()
        ->searching(false)->toBe($this->test)
        ->isSearching()->toBeFalse();
});

it('has term', function () {
    expect($this->test)
        ->getTerm()->toBeNull()
        ->term('test')->toBe($this->test)
        ->getTerm()->toBe('test');
});

it('without searches', function () {
    expect($this->test)
        ->isWithoutSearches()->toBeFalse()
        ->withoutSearches()->toBe($this->test)
        ->isWithoutSearches()->toBeTrue();
});

it('searches to array', function () {
    expect($this->test)
        ->withSearches([Search::make('name'), Search::make('price')])->toBe($this->test)
        ->searchesToArray()->toBeEmpty();

    expect($this->test->match())
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
    expect($this->test->match())
        ->withSearches([Search::make('name')])->toBe($this->test)
        ->searchesToArray()->toHaveCount(1)
        ->withoutSearches()->toBe($this->test)
        ->searchesToArray()->toBeEmpty();
});
