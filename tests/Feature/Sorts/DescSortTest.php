<?php

declare(strict_types=1);

use Honed\Refine\Sorts\DescSort;
use Honed\Refine\Sorts\Sort;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->name = 'created_at';
    $this->alias = 'oldest';
    $this->sort = DescSort::make($this->name)->alias($this->alias);
});

it('has desc sort', function () {
    expect($this->sort)
        ->enforcesDirection()->toBeTrue()
        ->getDirection()->toBe(Sort::DESCENDING);
});

it('does not apply', function () {
    $builder = Product::query();

    expect($this->sort)
        ->handle($builder, $this->alias, Sort::ASCENDING)->toBeFalse();

    expect($builder->getQuery()->orders)
        ->toBeEmpty();
});

it('applies', function () {
    $builder = Product::query();

    expect($this->sort)
        ->handle($builder, $this->alias, Sort::DESCENDING)->toBeTrue();

    expect($builder->getQuery()->orders)
        ->toBeOnlyOrder($this->name, Sort::DESCENDING);
});
