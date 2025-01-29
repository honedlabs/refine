<?php

declare(strict_types=1);

use Carbon\Carbon;
use Honed\Refine\Filters\DateFilter;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->param = 'created_at';
    $this->filter = DateFilter::make($this->param);
});

it('filters by date value', function () {
    $request = Request::create('/', 'GET', [$this->param => '01-01-2025']);

    expect($this->filter->apply($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'column'}->toBe($this->builder->qualifyColumn('created_at'))
        ->{'value'}->toBe('2025-01-01')
        ->{'operator'}->toBe('=')
        ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toEqual(Carbon::parse('01-01-2025'));
});

it('does not filter if not a date', function () {
    $request = Request::create('/', 'GET', [$this->param => 'test']);

    expect($this->filter->apply($this->builder, $request))
        ->toBeFalse();

    expect($this->builder->getQuery()->wheres)->toBeArray()->toBeEmpty();

    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
});
