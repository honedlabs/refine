<?php

declare(strict_types=1);

use Honed\Refine\Option;
use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\TrashedFilter;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->filter = TrashedFilter::new();
});

it('has trashed filter', function () {
    expect($this->filter)
        ->toBe($this->filter)
        ->getType()->toBe('trashed')
        ->getLabel()->toBe('Show deleted');
});

it('has options', function () {
    expect($this->filter)
        ->getOptions()->scoped(fn ($options) => $options
        ->toBeArray()
        ->toHaveCount(3)
        ->sequence(
            fn ($option) => $option
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe('with')
                ->getLabel()->toBe('With deleted'),
            fn ($option) => $option
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe('only')
                ->getLabel()->toBe('Only deleted'),
            fn ($option) => $option
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe('without')
                ->getLabel()->toBe('Without deleted')
        )
        );
});

it('applies with trashed', function () {
    $builder = Product::query();

    $request = Request::create('/', 'GET', [
        $this->filter->getParameter() => 'with',
    ]);

    expect($this->filter)
        ->refine($builder, $request)->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeEmpty();
});

it('applies only trashed', function () {
    $builder = Product::query();

    $request = Request::create('/', 'GET', [
        $this->filter->getParameter() => 'only',
    ]);

    expect($this->filter)
        ->refine($builder, $request)->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(1)
        ->{0}->toEqual([
            'type' => 'NotNull',
            'column' => $builder->qualifyColumn('deleted_at'),
            'boolean' => true,
        ]);
});

it('applies without trashed', function () {
    $builder = Product::query();

    $request = Request::create('/', 'GET', [
        $this->filter->getParameter() => 'default',
    ]);

    expect($this->filter)
        ->refine($builder, $request)->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(1)
        ->{0}->toEqual([
            'type' => 'Null',
            'column' => $builder->qualifyColumn('deleted_at'),
            'boolean' => 'and',
        ]);
});
