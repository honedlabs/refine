<?php

declare(strict_types=1);

use Honed\Refine\Sort;
use Honed\Refine\Filter;
use Honed\Refine\Refine;
use Honed\Refine\Search;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Auth\Access\Gate as AccessGate;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request as FacadesRequest;

beforeEach(function () {
    $this->test = Refine::make(Product::class);
});

it('has delimiter', function () {
    expect($this->test)
        ->hasDelimiter()->toBeFalse()
        ->getDelimiter()->toBe(config('refine.delimiter'))
        ->delimiter('|')->toBe($this->test)
        ->hasDelimiter()->toBeTrue()
        ->getDelimiter()->toBe('|');
});

it('can set as not refining', function () {
    expect($this->test)
        ->refining(false)->toBe($this->test)
        ->isFiltering()->toBeFalse()
        ->isSorting()->toBeFalse()
        ->isSearching()->toBeFalse();
});

it('has for', function () {
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

it('evaluates named closure dependencies', function () {
    $product = product();
    $request = FacadesRequest::create(route('products.show', $product), 'GET', ['key' => 'value']);

    expect($this->test->request($request)->for(Product::query()))
        ->evaluate(fn ($request) => $request->get('key'))->toBe('value')
        // ->evaluate(fn ($route) => $route)->toBeInstanceOf(Route::class)
        ->evaluate(fn ($builder) => $builder->getModel())->toBeInstanceOf(Product::class)
        ->evaluate(fn ($query) => $query->getModel())->toBeInstanceOf(Product::class)
        ->evaluate(fn ($product) => $product->getModel())->toBeInstanceOf(Product::class)
        ->evaluate(fn ($products) => $products->getModel())->toBeInstanceOf(Product::class);
});

it('evaluates typed closure dependencies', function () {
    $product = product();
    $request = FacadesRequest::create(route('products.show', $product), 'GET', ['key' => 'value']);

    expect($this->test->request($request)->for(Product::query()))
        ->evaluate(fn (Request $r) => $r->get('key'))->toBe('value')
        ->evaluate(fn (Builder $b) => $b->getModel())->toBeInstanceOf(Product::class)
        // ->evaluate(fn (Route $r) => $r)->toBeInstanceOf(Route::class)
        ->evaluate(fn (Gate $g) => $g)->toBeInstanceOf(AccessGate::class);
});

it('calls sorts', function () {
    expect($this->test)
        ->sorts([Sort::make('name', 'A-Z')])->toBe($this->test)
        ->getSorts()->toHaveCount(1);
});

it('calls filters', function () {
    expect($this->test)
        ->filters([Filter::make('name')])->toBe($this->test)
        ->getFilters()->toHaveCount(1);
});

it('calls searches', function () {
    expect($this->test)
        ->searches([Search::make('name')])->toBe($this->test)
        ->getSearches()->toHaveCount(1);
});

it('forwards calls to the builder', function () {
    expect($this->test)
        ->paginate(10)->toBeInstanceOf(LengthAwarePaginator::class)
        ->isRefined()->toBeTrue();
});

it('has array representation', function () {
    $this->test->with([
        Filter::make('name'),
        Sort::make('name'),
        Search::make('name'),
    ]);

    expect($this->test->toArray())->toBeArray()
        ->toHaveCount(4)
        ->toHaveKeys(['filters', 'sorts', 'searches', 'config'])
        ->{'config'}->scoped(fn ($config) => $config
            ->{'delimiter'}->toBe(config('refine.delimiter'))
            ->{'search'}->toBeNull()
            ->{'searches'}->toBe(config('refine.searches_key'))
            ->{'sorts'}->toBe(config('refine.sorts_key'))
            ->{'matches'}->toBe(config('refine.matches_key'))
        );
});

it('has array representation with matches', function () {
    $this->test->with([
        Filter::make('name'),
        Sort::make('name'),
        Search::make('name'),
    ])->match();

    expect($this->test->toArray())->toBeArray()
        ->toHaveCount(4)
        ->toHaveKeys(['filters', 'sorts', 'searches', 'config'])
        ->{'config'}->scoped(fn ($config) => $config
            ->{'delimiter'}->toBe(config('refine.delimiter'))
            ->{'search'}->toBeNull()
            ->{'searches'}->toBe(config('refine.searches_key'))
            ->{'sorts'}->toBe(config('refine.sorts_key'))
            ->{'matches'}->toBe(config('refine.matches_key'))
        );
});

it('refines once', function () {
    expect($this->test)
        ->refine()->toBe($this->test)
        ->isRefined()->toBeTrue()
        ->refine()->toBe($this->test)
        ->isRefined()->toBeTrue();
});


