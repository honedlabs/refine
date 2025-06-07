<?php

declare(strict_types=1);

use Honed\Refine\Concerns\HasSearches;
use Honed\Refine\Search;

beforeEach(function () {
    $this->test = new class()
    {
        use HasSearches;
    };
});

afterEach(function () {
    $this->test::useSearchKey('search');
    $this->test::useMatchKey('match');
    $this->test::shouldMatch(false);
});

it('is empty by default', function () {
    expect($this->test)
        ->isSearching()->toBeFalse()
        ->getSearches()->toBeEmpty();
});

it('adds searches', function () {
    expect($this->test)
        ->withSearches([Search::make('name')])->toBe($this->test)
        ->withSearches([Search::make('price')])->toBe($this->test)
        ->getSearches()->toHaveCount(2);
});

it('adds searches variadically', function () {
    expect($this->test)
        ->withSearches(Search::make('name'), Search::make('price'))->toBe($this->test)
        ->getSearches()->toHaveCount(2);
});

it('adds searches collection', function () {
    expect($this->test)
        ->withSearches(collect([Search::make('name'), Search::make('price')]))->toBe($this->test)
        ->getSearches()->toHaveCount(2);
});

it('has search key', function () {
    expect($this->test)
        ->getSearchKey()->toBe('search')
        ->searchKey('test')->toBe($this->test)
        ->getSearchKey()->toBe('test');
});

it('has search key globally', function () {
    $this->test::useSearchKey('test');

    expect($this->test)
        ->getSearchKey()->toBe('test');
});

it('has match key', function () {
    expect($this->test)
        ->getMatchKey()->toBe('match')
        ->matchKey('test')->toBe($this->test)
        ->getMatchKey()->toBe('test');
});

it('has match key globally', function () {
    $this->test::useMatchKey('test');

    expect($this->test)
        ->getMatchKey()->toBe('test');
});

it('should match', function () {
    expect($this->test)
        ->matches()->toBeFalse();

    expect($this->test->match())->toBe($this->test)
        ->matches()->toBeTrue();
});

it('should match globally', function () {
    $this->test::shouldMatch(true);

    expect($this->test)
        ->matches()->toBeTrue();
});

it('has term', function () {
    expect($this->test)
        ->getTerm()->toBeNull()
        ->term('test')->toBe($this->test)
        ->getTerm()->toBe('test');
});

it('enables and disables searching', function () {
    expect($this->test)
        // base case
        ->searchingEnabled()->toBeTrue()
        ->searchingDisabled()->toBeFalse()
        // disable
        ->disableSearching()->toBe($this->test)
        ->searchingEnabled()->toBeFalse()
        ->searchingDisabled()->toBeTrue()
        // enable
        ->enableSearching()->toBe($this->test)
        ->searchingEnabled()->toBeTrue()
        ->searchingDisabled()->toBeFalse();
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
        ->disableSearching()->toBe($this->test)
        ->searchesToArray()->toBeEmpty();
});
