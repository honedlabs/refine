<?php

declare(strict_types=1);

use Honed\Refine\Sorts\AscSort;
use Honed\Refine\Sorts\Sort;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->name = 'created_at';
    $this->alias = 'newest';
    $this->sort = AscSort::make($this->name)->alias($this->alias);
});

it('has asc sort', function () {
    expect($this->sort)
        ->enforcesDirection()->toBeTrue()
        ->getDirection()->toBe(Sort::ASCENDING)
        ->getType()->toBe(Sort::ASCENDING);
});

it('does not apply', function () {
    expect($this->sort)
        ->handle($this->builder, $this->alias, Sort::DESCENDING)->toBeFalse();

    expect($this->builder->getQuery()->orders)
        ->toBeEmpty();
});

it('applies', function () {
    expect($this->sort)
        ->handle($this->builder, $this->alias, Sort::ASCENDING)->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->name, Sort::ASCENDING);
});
