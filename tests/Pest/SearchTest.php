<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Searches\Search;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->param = 'name';
    $this->search = Search::make($this->param);
    $this->key = Refine::SearchKey;
});

it('searches', function () {
    $request = Request::create('/', 'GET', [$this->key => 'test']);

    expect($this->search)
        ->apply($this->builder, $request, $this->key, true, true)->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'column'}->toBe($this->builder->qualifyColumn('name'))
        ->{'value'}->toBe('%test%')
        ->{'operator'}->toBe('like')
        ->{'boolean'}->toBe('and')
        );
});

it('changes query boolean', function () {
    $request = Request::create('/', 'GET', [$this->key => 'test']);

    expect($this->search)
        ->apply($this->builder, $request, $this->key, true, false)->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'column'}->toBe($this->builder->qualifyColumn('name'))
        ->{'value'}->toBe('%test%')
        ->{'operator'}->toBe('like')
        ->{'boolean'}->toBe('or')
        );
}
);

it('prevents searching if no value is provided', function () {
    $request = Request::create('/', 'GET', [$this->key => '']);

    expect($this->search->apply($this->builder, $request, $this->key, true, true))->toBeFalse();

    expect($this->builder->getQuery()->wheres)->toBeEmpty();
});

it('prevents searching if key does not match', function () {
    $key = 'other';

    $request = Request::create('/', 'GET', [$key => 'test']);

    expect($this->search->apply($this->builder, $request, $this->key, true, true))->toBeFalse();

    expect($this->builder->getQuery()->wheres)->toBeEmpty();
});

it('only runs if it is in array', function () {
    $request = Request::create('/', 'GET', [$this->key => 'test']);

    $columns = [$this->param, 'description'];

    expect($this->search)
        ->apply($this->builder, $request, $this->key, $columns, true)->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'column'}->toBe($this->builder->qualifyColumn('name'))
        ->{'value'}->toBe('%test%')
        ->{'operator'}->toBe('like')
        ->{'boolean'}->toBe('and')
        );
});
