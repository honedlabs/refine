<?php

declare(strict_types=1);

use Honed\Refine\Sort;
use Honed\Refine\Filter;
use Honed\Refine\Refine;
use Honed\Refine\Search;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->test = Refine::make(Product::class);
});

it('has a sorts key', function () {
    expect($this->test)
        ->getSortsKey()->toBe(config('refine.sorts_key'))
        ->sortsKey('test')->toBe($this->test)
        ->getSortsKey()->toBe('test');
});

it('has a searches key', function () {
    expect($this->test)
        ->getSearchesKey()->toBe(config('refine.searches_key'))
        ->searchesKey('test')->toBe($this->test)
        ->getSearchesKey()->toBe('test');
});

it('can match', function () {
    expect($this->test)
        ->isMatching()->toBe(config('refine.match'));

    expect($this->test->match())->toBe($this->test)
        ->isMatching()->toBeTrue();
});

it('has a delimiter', function () {
    expect($this->test)
        ->getDelimiter()->toBe(config('refine.delimiter'))
        ->delimiter('|')->toBe($this->test)
        ->getDelimiter()->toBe('|');
});

it('can set as not refining', function () {
    expect($this->test)
        ->withoutRefining()->toBe($this->test)
        ->isWithoutFiltering()->toBeTrue()
        ->isWithoutSorting()->toBeTrue()
        ->isWithoutSearching()->toBeTrue();
});

it('has array representation', function () {
    $this->test->using([
        Filter::make('name'),
        Sort::make('name'),
        Search::make('name'),
    ]);

    expect($this->test->toArray())->toBeArray()
        ->toHaveCount(3)
        ->toHaveKey('filters')
        ->toHaveKey('sorts')
        ->toHaveKey('config')
        ->{'config'}->scoped(fn ($config) => $config
            ->{'delimiter'}->toBe(config('refine.delimiter'))
            ->{'search'}->toBeNull()
            ->{'searches'}->toBe(config('refine.searches_key'))
            ->{'sorts'}->toBe(config('refine.sorts_key'))
        );
});

it('has array representation with matches', function () {
    $this->test->using([
        Filter::make('name'),
        Sort::make('name'),
        Search::make('name'),
    ])->match();

    expect($this->test->toArray())->toBeArray()
        ->toHaveCount(4)
        ->toHaveKey('filters')
        ->toHaveKey('sorts')
        ->toHaveKey('searches')
        ->toHaveKey('config')
        ->{'config'}->scoped(fn ($config) => $config
            ->{'delimiter'}->toBe(config('refine.delimiter'))
            ->{'search'}->toBeNull()
            ->{'searches'}->toBe(config('refine.searches_key'))
            ->{'sorts'}->toBe(config('refine.sorts_key'))
            ->{'matches'}->toBe(config('refine.matches_key'))
        );
});

it('has for method', function () {
    expect($this->test)
        ->for(Product::class)->toBe($this->test)
        ->getFor()->toBeInstanceOf(Builder::class);
});

it('has before method', function () {
    expect($this->test)
        ->before(function () {
            return $this->test;
        })->toBe($this->test);
});

it('has after method', function () {
    expect($this->test)
        ->after(function () {
            return $this->test;
        })->toBe($this->test);
});

it('calls sorts', function () {
    expect($this->test)
        ->sorts([Sort::make('name', 'A-Z')])->toBe($this->test)
        ->getSorts()->toHaveCount(1);
});

it('has filters method', function () {
    expect($this->test)
        ->filters([Filter::make('name')])->toBe($this->test)
        ->getFilters()->toHaveCount(1);
});

it('has searches method', function () {
    expect($this->test)
        ->searches([Search::make('name')])->toBe($this->test)
        ->getSearches()->toHaveCount(1);
});

it('forwards calls to the builder', function () {
    expect($this->test)
        ->paginate(10)->toBeInstanceOf(LengthAwarePaginator::class)
        ->isRefined()->toBeTrue();
});