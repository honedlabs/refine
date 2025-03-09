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

it('has next direction', function () {
    expect($this->sort)
        ->getNextDirection()->toBe($this->sort->getAscendingValue());
});

it('can invert direction', function () {
    expect($this->sort)
        ->isInverted()->toBeFalse()
        ->invert()->toBe($this->sort)
        ->isInverted()->toBeTrue()
        ->getNextDirection()->toBe($this->sort->getDescendingValue());
});

it('has direction', function () {
    expect($this->sort)
        ->getAscendingValue()->toBe($this->param)
        ->getDescendingValue()->toBe('-'.$this->param);
});

it('has array representation', function () {
    expect($this->sort->toArray())->toEqual([
        'name' => $this->param,
        'label' => ucfirst($this->param),
        'type' => 'sort',
        'meta' => [],
        'active' => false,
        'direction' => null,
        'next' => $this->sort->getAscendingValue(),
    ]);
});
