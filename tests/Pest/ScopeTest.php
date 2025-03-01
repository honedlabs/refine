<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Sorts\Sort;
use Honed\Refine\Filters\Filter;
use Honed\Refine\Searches\Search;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

// This case verifies that query parameters can be scoped

beforeEach(function () {
    $this->builder = Product::query();

    $this->refine = Refine::make($this->builder)
        ->scope('products')
        ->match();
});

it('applies sort when scoped parameter is provided', function () {
    $value = 'name';

    $this->refine->sorts([Sort::make($value)]);

    $param = $this->refine->formatScope($this->refine->getSortsKey());

    $request = Request::create('/', 'GET', [$param => $value]);

    $this->refine->request($request)->refine();

    expect($this->builder->getQuery()->orders)
        ->toHaveCount(1);

    expect($this->refine->getSorts())
        ->{0}->isActive()->toBeTrue();
});

it('does not apply sort when scoped parameter is not provided', function () {
    $value = 'name';

    $this->refine->sorts([Sort::make($value)]);

    $param = $this->refine->getSortsKey();

    $request = Request::create('/', 'GET', [$param => $value]);

    $this->refine->request($request)->refine();

    expect($this->builder->getQuery()->orders)
        ->toBeNull();

    expect($this->refine->getSorts())
        ->{0}->isActive()->toBeFalse();
});

it('applies search when scoped parameter is provided', function () {
    $this->refine->searches([Search::make('name'), Search::make('description')]);

    $searchParam = $this->refine->formatScope($this->refine->getSearchesKey());
    $searchValue = 'search';

    $matchesParam = $this->refine->formatScope($this->refine->getMatchesKey());
    $matchesValue = 'name';

    $request = Request::create('/', 'GET', [
        $searchParam => $searchValue,
        $matchesParam => $matchesValue,
    ]);

    $this->refine->request($request)->refine();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1);
});

it('does not apply search when scoped parameter is not provided', function () {
    $this->refine->searches([Search::make('name'), Search::make('description')]);

    $searchParam = $this->refine->getSearchesKey();
    $searchValue = 'search';

    $matchesParam = $this->refine->getMatchesKey();
    $matchesValue = 'name';

    $request = Request::create('/', 'GET', [
        $searchParam => $searchValue,
        $matchesParam => $matchesValue,
    ]);

    $this->refine->request($request)->refine();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();
});

it('applies filter when scoped parameter is provided', function () {
    $param = 'name';

    $this->refine->filters([Filter::make($param)]);

    $param = $this->refine->formatScope($param);

    $value = 'test';

    $request = Request::create('/', 'GET', [$param => $value]);

    $this->refine->request($request)->refine();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1);
});

it('does not apply filter when scoped parameter is not provided', function () {
    $param = 'name';

    $this->refine->filters([Filter::make($param)]);

    $value = 'test';

    $request = Request::create('/', 'GET', [$param => $value]);

    $this->refine->request($request)->refine();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();
});