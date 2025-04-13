<?php

declare(strict_types=1);

use Honed\Refine\DescSort;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->builder = Product::query();

    $this->sort = DescSort::make('created_at')
        ->alias('oldest');
});

it('has desc sort', function () {
    expect($this->sort)
        ->isFixed()->toBeTrue()
        ->getDirection()->toBe('desc')
        ->getType()->toBe('desc');
});

it('does not apply', function () {
    $builder = Product::query();

    expect($this->sort)
        ->refine($builder, ['invalid', 'asc'])->toBeFalse();

    expect($builder->getQuery()->orders)
        ->toBeEmpty();
});

it('applies', function () {
    $builder = Product::query();

    expect($this->sort)
        ->refine($builder, ['oldest', 'asc'])->toBeTrue();

    expect($builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn('created_at'), 'desc');
});
