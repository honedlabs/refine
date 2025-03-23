<?php

declare(strict_types=1);

use Honed\Refine\Sort;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->param = 'name';
    $this->builder = Product::query();
    $this->sort = Sort::make($this->param);
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
        ->interpretsAscendingValue()->toBe($this->param)
        ->getDescendingValue()->toBe('-'.$this->param);
});

it('has next direction', function () {
    expect($this->sort)
        ->getNextDirection()->toBe($this->sort->interpretsAscendingValue());
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
        ->only('asc')->toBe($this->sort)
        ->isFixed()->toBeTrue()
        ->getDirection()->toBe('asc');
});