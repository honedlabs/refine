<?php

declare(strict_types=1);

use Honed\Refine\Filters\TrashedFilter;
use Honed\Refine\Option;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->filter = TrashedFilter::new();
});

it('has trashed filter', function () {
    expect($this->filter)
        ->toBe($this->filter)
        ->getName()->toBe('trashed')
        ->getType()->toBe('select')
        ->getLabel()->toBe(__('refine::filters.trashed.label'))
        ->getTrueLabel()->toBe(__('refine::filters.trashed.true'))
        ->getFalseLabel()->toBe(__('refine::filters.trashed.false'))
        ->getBlankLabel()->toBe(__('refine::filters.trashed.blank'));
});

it('has options', function () {
    expect($this->filter)
        ->getOptions()->scoped(fn ($options) => $options
        ->toBeArray()
        ->toHaveCount(3)
        ->sequence(
            fn ($option) => $option
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe('all')
                ->getLabel()->toBe(__('refine::filters.trashed.blank')),
            fn ($option) => $option
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe('true')
                ->getLabel()->toBe(__('refine::filters.trashed.true')),
            fn ($option) => $option
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe('false')
                ->getLabel()->toBe(__('refine::filters.trashed.false')),
        )
        );
});

it('applies with trashed', function () {
    $builder = Product::query();

    expect($this->filter)
        ->handle($builder, 'true')->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeEmpty();
});

it('applies only trashed', function () {
    $builder = Product::query();

    expect($this->filter)
        ->handle($builder, 'false')->toBeTrue();

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

    expect($this->filter)
        ->handle($builder, 'blank')->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(1)
        ->{0}->toEqual([
            'type' => 'Null',
            'column' => $builder->qualifyColumn('deleted_at'),
            'boolean' => 'and',
        ]);
});
