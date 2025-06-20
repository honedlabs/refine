<?php

declare(strict_types=1);

use Honed\Refine\Sorts\Sort;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->sort = Sort::make('name');
});

it('has direction', function () {
    expect($this->sort)
        ->getDirection()->toBeNull()
        ->direction(Sort::ASCENDING)->toBe($this->sort)
        ->getDirection()->toBe(Sort::ASCENDING);
});

it('can be ascending', function () {
    expect($this->sort)
        ->isAscending()->toBeFalse()
        ->ascending()->toBe($this->sort)
        ->isAscending()->toBeTrue()
        ->asc()->toBe($this->sort)
        ->isAscending()->toBeTrue();
});

it('can be descending', function () {
    expect($this->sort)
        ->isDescending()->toBeFalse()
        ->descending()->toBe($this->sort)
        ->isDescending()->toBeTrue()
        ->desc()->toBe($this->sort)
        ->isDescending()->toBeTrue();
});

it('can invert', function () {
    expect($this->sort)
        ->isInverted()->toBeFalse()
        ->invert()->toBe($this->sort)
        ->isInverted()->toBeTrue();
});
