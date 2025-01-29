<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->param = 'name';
    $this->filter = Filter::make($this->param);
});

it('filters by exact value', function () {
    $request = Request::create('/', 'GET', [$this->param => 'test']);

    $this->filter->apply($this->builder, $request);

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'column'}->toBe($this->builder->qualifyColumn('name'))
        ->{'value'}->toBe('test')
        ->{'operator'}->toBe('=')
        ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe('test');
});

it('does not filter if no value', function () {
    $request = Request::create('/', 'GET', ['other' => 'test']);

    $this->filter->apply($this->builder, $request);

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toBeEmpty();

    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
});

it('filters using like mode', function () {
    $request = request()->merge([$this->param => 'test']);

    $this->filter->like()->apply($this->builder, $request);

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'type'}->toBe('raw')
        ->{'sql'}->toBe(\sprintf('LOWER(%s) LIKE ?', $this->builder->qualifyColumn('name')))
        ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe('test');
});

it('filters using starts with mode', function () {
    $request = request()->merge(['name' => 'test']);

    $this->filter->startsWith()->apply($this->builder, $request);

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'type'}->toBe('raw')
        ->{'sql'}->toBe(\sprintf('%s LIKE ?', $this->builder->qualifyColumn('name')))
        ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe('test');
});

it('filters using ends with mode', function () {
    $request = request()->merge(['name' => 'test']);

    $this->filter->endsWith()->apply($this->builder, $request);

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'type'}->toBe('raw')
        ->{'sql'}->toBe(\sprintf('%s LIKE ?', $this->builder->qualifyColumn('name')))
        ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe('test');
});

it('filters using similarity checks and inversion', function () {
    $request = request()->merge(['name' => 'test']);

    $this->filter->not()->like()->apply($this->builder, $request);

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'type'}->toBe('raw')
        ->{'sql'}->toBe(\sprintf('LOWER(%s) NOT LIKE ?', $this->builder->qualifyColumn('name')))
        ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe('test');
});

it('throws an exception if an invalid operator is provided', function () {
    $request = Request::create('/', 'GET', [$this->param => 'test']);

    $this->filter->gt()->like()->apply($this->builder, $request);
})->throws(\InvalidArgumentException::class);

it('has array representation', function () {
    expect($this->filter->toArray())->toEqual([
        'name' => 'name',
        'label' => 'Name',
        'type' => 'filter',
        'meta' => [],
        'active' => false,
        'value' => null,
    ]);
});

it('handles different modes', function () {
    expect($this->filter)
        ->exact()->getMode()->toBe(Filter::Exact)
        ->like()->getMode()->toBe(Filter::Like)
        ->startsWith()->getMode()->toBe(Filter::StartsWith)
        ->endsWith()->getMode()->toBe(Filter::EndsWith);
});

it('handles different operators', function () {
    expect($this->filter->not())
        ->toBe($this->filter)
        ->getOperator()->toBe(Filter::Not);

    expect($this->filter->gt())
        ->toBe($this->filter)
        ->getOperator()->toBe(Filter::GreaterThan);

    expect($this->filter->lt())
        ->toBe($this->filter)
        ->getOperator()->toBe(Filter::LessThan);
});
