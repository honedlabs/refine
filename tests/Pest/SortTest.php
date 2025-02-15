<?php

declare(strict_types=1);

use Honed\Refine\Sorts\Sort;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->sort = Sort::make('name');
    $this->key = config('refine.sorts');
});

it('sorts by attribute', function () {
    $request = Request::create('/', 'GET', [$this->key => 'name']);

    expect($this->sort->apply($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'column'}->toBe($this->builder->qualifyColumn('name'))
        ->{'direction'}->toBe('asc')
        );

    expect($this->sort)
        ->isActive()->toBeTrue()
        ->getNextDirection()->toBe('-name')
        ->getDirection()->toBe('asc');
});

it('can enforce a singular direction', function () {
    $request = Request::create('/', 'GET', [$this->key => 'name']);

    expect($this->sort)
        ->isSingularDirection()->toBeFalse()
        ->desc()->toBe($this->sort)
        ->isSingularDirection()->toBeTrue();

    expect($this->sort->apply($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'column'}->toBe($this->builder->qualifyColumn('name'))
        ->{'direction'}->toBe('desc')
        );

    expect($this->sort)
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('desc')
        ->getNextDirection()->toBe('-name');
});

it('does not sort if no value', function () {
    $request = Request::create('/', 'GET', ['order' => 'test']);

    expect($this->sort->apply($this->builder, $request, $this->key))
        ->toBeFalse();

    expect($this->builder->getQuery()->orders)->toBeNull();
});

it('has direction', function () {
    expect($this->sort)
        ->getAscendingValue()->toBe('name')
        ->getDescendingValue()->toBe('-name');
});

it('has array representation', function () {
    expect($this->sort->toArray())->toEqual([
        'name' => 'name',
        'label' => 'Name',
        'type' => 'sort',
        'meta' => [],
        'active' => false,
        'direction' => null,
        'next' => 'name',
    ]);
});

it('has next direction', function () {
    expect($this->sort)
        ->getNextDirection()->toBe('name')
        ->direction('asc')->getNextDirection()->toBe('-name')
        ->direction('desc')->getNextDirection()->toBeNull();
});
