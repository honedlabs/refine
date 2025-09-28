<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;
use Honed\Refine\Filters\TernaryFilter;
use Honed\Refine\Option;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->filter = TernaryFilter::make('best_seller');
});

it('has set up', function () {
    expect($this->filter)
        ->getType()->toBe('select')
        ->getDefault()->toBe('blank');
});

it('has default values', function () {
    expect($this->filter)
        ->getDefault()->toBe('blank')
        ->defaultToTrue()->toBe($this->filter)
        ->getDefault()->toBe('true')
        ->defaultToFalse()->toBe($this->filter)
        ->getDefault()->toBe('false')
        ->defaultToBlank()->toBe($this->filter)
        ->getDefault()->toBe('blank');
});

it('has blank label', function () {
    expect($this->filter)
        ->getBlankLabel()->toBe('All')
        ->blankLabel('All sellers')->toBe($this->filter)
        ->getBlankLabel()->toBe('All sellers');
});

it('has true label', function () {
    expect($this->filter)
        ->getTrueLabel()->toBe('True')
        ->trueLabel('Best sellers')->toBe($this->filter)
        ->getTrueLabel()->toBe('Best sellers');
});

it('has false label', function () {
    expect($this->filter)
        ->getFalseLabel()->toBe('False')
        ->falseLabel('Worst sellers')->toBe($this->filter)
        ->getFalseLabel()->toBe('Worst sellers');
});

it('has blank query', function () {
    expect($this->filter)
        ->getBlankQuery()->toBeNull()
        ->blankQuery(fn ($builder) => $builder->where('best_seller', true))->toBe($this->filter)
        ->getBlankQuery()->toBeInstanceOf(Closure::class);
});

it('has true query', function () {
    expect($this->filter)
        ->getTrueQuery()->toBeNull()
        ->trueQuery(fn ($builder) => $builder->where('best_seller', true))->toBe($this->filter)
        ->getTrueQuery()->toBeInstanceOf(Closure::class);
});

it('has false query', function () {
    expect($this->filter)
        ->getFalseQuery()->toBeNull()
        ->falseQuery(fn ($builder) => $builder->where('best_seller', false))->toBe($this->filter)
        ->getFalseQuery()->toBeInstanceOf(Closure::class);
});

it('has queries', function () {
    expect($this->filter)
        ->queries(
            true: fn ($builder) => $builder->where('best_seller', true),
            false: fn ($builder) => $builder->where('best_seller', false),
            blank: fn ($builder) => $builder->where('best_seller', null)
        )->toBe($this->filter)
        ->getTrueQuery()->toBeInstanceOf(Closure::class)
        ->getFalseQuery()->toBeInstanceOf(Closure::class)
        ->getBlankQuery()->toBeInstanceOf(Closure::class);
});

it('has options', function () {
    expect($this->filter)
        ->getOptions()
        ->scoped(fn ($options) => $options
            ->toBeArray()
            ->toHaveCount(3)
            ->sequence(
                fn ($option) => $option
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe('all')
                    ->getLabel()->toBe('All'),
                fn ($option) => $option
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe('true')
                    ->getLabel()->toBe('True'),
                fn ($option) => $option
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe('false')
                    ->getLabel()->toBe('False')
            )
        );
});

it('has options with custom labels', function () {
    expect($this->filter)
        ->blankLabel('All sellers')->toBe($this->filter)
        ->trueLabel('Best sellers')->toBe($this->filter)
        ->falseLabel('Worst sellers')->toBe($this->filter)
        ->getOptions()->scoped(fn ($options) => $options
        ->toBeArray()
        ->toHaveCount(3)
        ->sequence(
            fn ($option) => $option
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe('all')
                ->getLabel()->toBe('All sellers'),
            fn ($option) => $option
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe('true')
                ->getLabel()->toBe('Best sellers'),
            fn ($option) => $option
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe('false')
                ->getLabel()->toBe('Worst sellers')
        )
        );
});

it('applies default blank query', function () {
    $builder = Product::query();

    expect($this->filter)
        ->handle($builder, 'all')->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeEmpty();
});

it('applies custom blank query', function () {
    $builder = Product::query();

    expect($this->filter)
        ->blankQuery(fn ($builder) => $builder->where('best_seller', true))->toBe($this->filter)
        ->handle($builder, 'all')->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeOnlyWhere('best_seller', true);
});

it('applies default true query', function () {
    $builder = Product::query();

    expect($this->filter)
        ->handle($builder, 'true')->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeOnlyWhere('best_seller', true);
});

it('applies custom true query', function () {
    $builder = Product::query();

    expect($this->filter)
        ->trueQuery(fn ($builder) => $builder->where('name', true))->toBe($this->filter)
        ->handle($builder, 'true')->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeOnlyWhere('name', true);
});

it('applies default false query', function () {
    $builder = Product::query();

    expect($this->filter)
        ->handle($builder, 'false')->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeOnlyWhere('best_seller', false);
});

it('applies custom false query', function () {
    $builder = Product::query();

    expect($this->filter)
        ->falseQuery(fn ($builder) => $builder->where('name', false))->toBe($this->filter)
        ->handle($builder, 'false')->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeOnlyWhere('name', false);
});

// it('applies only trashed', function () {
//     $builder = Product::query();

//     expect($this->filter)
//         ->handle($builder, 'only')->toBeTrue();

//     expect($builder->getQuery()->wheres)
//         ->toBeArray()
//         ->toHaveCount(1)
//         ->{0}->toEqual([
//             'type' => 'NotNull',
//             'column' => $builder->qualifyColumn('deleted_at'),
//             'boolean' => true,
//         ]);
// });

// it('applies without trashed', function () {
//     $builder = Product::query();

//     expect($this->filter)
//         ->handle($builder, 'without')->toBeTrue();

//     expect($builder->getQuery()->wheres)
//         ->toBeArray()
//         ->toHaveCount(1)
//         ->{0}->toEqual([
//             'type' => 'Null',
//             'column' => $builder->qualifyColumn('deleted_at'),
//             'boolean' => 'and',
//         ]);
// });
