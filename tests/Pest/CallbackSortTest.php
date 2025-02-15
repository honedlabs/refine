<?php

declare(strict_types=1);

use Honed\Refine\Sorts\CallbackSort;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->param = 'name';
    $this->sort = CallbackSort::make($this->param);
    $this->fn = fn ($builder, $direction) => $builder->orderBy('description', $direction);
    $this->key = config('refine.sorts');
});

it('fails if no callback is set', function () {
    $request = Request::create('/', 'GET', [$this->key => 'name']);
    $this->sort->apply($this->builder, $request, $this->key);
})->throws(\InvalidArgumentException::class);

it('sorts with callback', function () {
    $request = Request::create('/', 'GET', [$this->key => '-name']);

    expect($this->sort->callback($this->fn)->apply($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'column'}->toBe('description')
        ->{'direction'}->toBe('desc')
        );

    expect($this->sort)
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('desc');
});

it('does not sort if no value', function () {
    $request = Request::create('/', 'GET', ['order' => 'test']);

    expect($this->sort->apply($this->builder, $request, $this->key))
        ->toBeFalse();

    expect($this->builder->getQuery()->orders)->toBeNull();

    expect($this->sort)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
});
