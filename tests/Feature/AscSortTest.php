<?php

declare(strict_types=1);

use Honed\Refine\AscSort;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();

    $this->sort = AscSort::make('created_at')
        ->alias('newest');
});

it('has asc sort', function () {
    expect($this->sort)
        ->isFixed()->toBeTrue()
        ->getDirection()->toBe('asc')
        ->getType()->toBe('asc');
});

it('does not apply', function () {
    expect($this->sort)
        ->refine($this->builder, ['invalid', 'asc'])->toBeFalse();

    expect($this->builder->getQuery()->orders)
        ->toBeEmpty();
});

it('applies', function () {
    expect($this->sort)
        ->refine($this->builder, ['newest', 'asc'])->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder('created_at', 'asc');
});
