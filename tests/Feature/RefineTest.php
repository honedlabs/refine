<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Illuminate\Auth\Access\Gate as AccessGate;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Workbench\App\Models\Product;
use Workbench\App\Refiners\RefineProduct;

beforeEach(function () {
    $this->test = Refine::make(Product::class);
});

afterEach(function () {
    Refine::flushState();
});

it('forwards calls to the builder', function () {
    expect($this->test)
        ->paginate(10)->toBeInstanceOf(LengthAwarePaginator::class)
        ->isCompleted()->toBeTrue();
});

it('has macro', function () {
    $this->test->macro('test', fn () => 'test');

    expect($this->test)
        ->test()->toBe('test');
});

it('resolves refiner name from model', function () {
    expect(Refine::resolveRefinerName(Product::class))
        ->toBe('App\Refiners\RefineModels\Product');

    Refine::guessRefinersUsing(fn ($className) => Str::of($className)
        ->afterLast('\\')
        ->prepend('Workbench\App\Refiners\Refine')
        ->toString()
    );

    expect(Refine::resolveRefinerName(Product::class))
        ->toBe(RefineProduct::class);
});

it('can use a custom namespace', function () {
    Refine::useNamespace('Workbench\App\\');

    expect(Refine::resolveRefinerName(Product::class))
        ->toBe('Workbench\App\RefineModels\Product');
});

it('has array representation', function () {
    expect($this->test->toArray())->toBeArray()
        ->toHaveKeys([
            'sort',
            'search',
            'match',
            'term',
            'delimiter',
            'placeholder',
            'filters',
            'sorts',
            'searches',
        ]);
});

it('serializes to json', function () {
    expect($this->test->jsonSerialize())
        ->toHaveKeys([
            'sort',
            'search',
            'match',
            'delimiter',
            'sorts',
            'filters',
            'searches',
        ]);
});

it('evaluates closure dependencies', function ($callback, $type) {
    expect($this->test)
        ->evaluate($callback)->toBeInstanceOf($type);
})->with([
    fn () => [fn ($request) => $request, Request::class],
    fn () => [fn ($refine) => $refine, Refine::class],
    fn () => [fn ($builder) => $builder, Builder::class],
    fn () => [fn ($query) => $query, Builder::class],
    fn () => [fn ($q) => $q, Builder::class],
    fn () => [fn (Request $arg) => $arg, Request::class],
    fn () => [fn (Refine $arg) => $arg, Refine::class],
    fn () => [fn (Builder $arg) => $arg, Builder::class],
    fn () => [fn (Gate $arg) => $arg, AccessGate::class],
]);
