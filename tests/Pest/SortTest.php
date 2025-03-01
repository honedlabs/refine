<?php

declare(strict_types=1);

use Honed\Refine\Sorts\Sort;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->param = 'name';
    $this->builder = Product::query();
    $this->sort = Sort::make($this->param);
    $this->key = config('refine.config.sorts');
});

it('sorts by attribute', function () {
    $request = Request::create('/', 'GET', [$this->key => $this->param]);

    expect($this->sort->apply($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
            ->{'column'}->toBe($this->builder->qualifyColumn($this->param))
            ->{'direction'}->toBe(Sort::ASCENDING)
        );

    expect($this->sort)
        ->isActive()->toBeTrue()
        ->getNextDirection()->toBe('-name')
        ->getDirection()->toBe(Sort::ASCENDING);
});

it('can enforce a singular direction', function () {
    $request = Request::create('/', 'GET', [$this->key => $this->param]);

    expect($this->sort)
        ->isSingularDirection()->toBeFalse()
        ->desc()->toBe($this->sort)
        ->isSingularDirection()->toBeTrue();

    expect($this->sort->apply($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
            ->{'column'}->toBe($this->builder->qualifyColumn($this->param))
            ->{'direction'}->toBe(Sort::DESCENDING)
        );

    expect($this->sort)
        ->isActive()->toBeTrue()
        ->getDirection()->toBe(Sort::DESCENDING)
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
        ->getAscendingValue()->toBe($this->param)
        ->getDescendingValue()->toBe('-'.$this->param);
});

it('has array representation', function () {
    expect($this->sort->toArray())->toEqual([
        'name' => $this->param,
        'label' => ucfirst($this->param),
        'type' => 'sort',
        'meta' => [],
        'active' => false,
        'direction' => null,
        'next' => $this->sort->getAscendingValue(),
    ]);
});

it('has next direction', function () {
    expect($this->sort)
        ->getNextDirection()->toBe($this->sort->getAscendingValue())
        ->direction(Sort::ASCENDING)
        ->getNextDirection()->toBe($this->sort->getDescendingValue())
        ->direction(Sort::DESCENDING)
        ->getNextDirection()->toBeNull();
});

it('can invert direction', function () {
    expect($this->sort)
        ->invert()->toBe($this->sort)
        ->isInverted()->toBeTrue()
        ->getNextDirection()->toBe($this->sort->getDescendingValue());
});
