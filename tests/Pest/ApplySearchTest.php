<?php

declare(strict_types=1);

use Honed\Refine\Search;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->search = 'search term';
    $this->key = config('refine.key.searches');
});

it('applies', function () {
    $name = 'name';

    $search = Search::make($name);

    expect($search)
        ->refine($this->builder, 'search term', null, 'and')->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch($this->builder->qualifyColumn($name));

    expect($search)
        ->isActive()->toBeTrue()
        ->getValue()->toBe('search term');
});

it('changes query boolean', function () {
    $name = 'name';

    $search = Search::make($name);

    expect($search)
        ->refine($this->builder, 'test', null, 'or')->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toBeOnlySearch($this->builder->qualifyColumn($name), 'or');
});

it('prevents searching if no value is provided', function () {
    $name = 'name';

    $search = Search::make($name);

    expect($search->refine($this->builder, null, null, 'and'))->toBeFalse();

    expect($this->builder->getQuery()->wheres)->toBeEmpty();
});

it('only executes if it is in array', function () {
    $name = 'name';
    $columns = [$name, 'description'];

    $search = Search::make($name);

    expect($search)
        ->refine($this->builder, 'test', $columns, 'and')->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toBeOnlySearch($this->builder->qualifyColumn($name), 'and');
});
