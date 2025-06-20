<?php

declare(strict_types=1);

use Honed\Refine\Sorts\Sort;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->name = 'name';
    $this->sort = Sort::make('name');
});

it('has direction', function () {
    expect($this->sort)
        ->getDirection()->toBeNull()
        ->direction(Sort::ASCENDING)->toBe($this->sort)
        ->getDirection()->toBe(Sort::ASCENDING)
        ->isAscending()->toBeTrue()
        ->isDescending()->toBeFalse()
        ->direction(Sort::DESCENDING)->toBe($this->sort)
        ->getDirection()->toBe(Sort::DESCENDING)
        ->isAscending()->toBeFalse()
        ->isDescending()->toBeTrue();
});

it('has parameter', function () {
    expect($this->sort)
        ->getAscendingValue()->toBe($this->name)
        ->getDescendingValue()->toBe('-'.$this->name);
});

it('has next direction', function () {
    expect($this->sort)
        ->getNextDirection()->toBe($this->sort->getAscendingValue());
});
