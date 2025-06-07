<?php

declare(strict_types=1);

use Honed\Refine\Sort;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->sort = Sort::make('name');
});

afterEach(function () {
    Sort::flushState();
});

it('has direction', function () {
    expect($this->sort)
        ->getDirection()->toBeNull()
        ->direction('asc')->toBe($this->sort)
        ->getDirection()->toBe('asc')
        ->isAscending()->toBeTrue()
        ->isDescending()->toBeFalse()
        ->direction('desc')->toBe($this->sort)
        ->getDirection()->toBe('desc')
        ->isAscending()->toBeFalse()
        ->isDescending()->toBeTrue();
});

it('has parameter', function () {
    expect($this->sort)
        ->getAscendingValue()->toBe('name')
        ->getDescendingValue()->toBe('-name');
});

it('has next direction', function () {
    expect($this->sort)
        ->getNextDirection()->toBe($this->sort->getAscendingValue());
});

it('can invert', function () {
    expect($this->sort)
        ->isInverted()->toBeFalse()
        ->invert()->toBe($this->sort)
        ->isInverted()->toBeTrue()
        ->getNextDirection()->toBe($this->sort->getDescendingValue());
});

it('can be fixed', function () {
    expect($this->sort)
        ->isFixed()->toBeFalse()
        ->fixed('asc')->toBe($this->sort)
        ->isFixed()->toBeTrue()
        ->getDirection()->toBe('asc');
});
