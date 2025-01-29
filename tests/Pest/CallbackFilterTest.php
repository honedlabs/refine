<?php

declare(strict_types=1);

use Honed\Refine\Filters\CallbackFilter;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->param = 'name';
    $this->filter = CallbackFilter::make($this->param);
    $this->fn = fn ($builder, $value) => $builder->where('description', $value);
});

it('fails if no callback is set', function () {
    $request = Request::create('/', 'GET', [$this->param => 'test']);

    $this->filter->apply($this->builder, $request);

})->throws(\InvalidArgumentException::class);

it('filters with callback', function () {
    $request = Request::create('/', 'GET', [$this->param => 'test']);

    expect($this->filter->callback($this->fn)->apply($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'column'}->toBe('description')
        ->{'value'}->toBe('test')
        ->{'operator'}->toBe('=')
        ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe('test');
});

it('does not filter if no value', function () {
    $request = Request::create('/', 'GET', [$this->param => null]);

    expect($this->filter->apply($this->builder, $request))
        ->toBeFalse();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toBeEmpty();

    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
});
