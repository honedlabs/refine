<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->test = Refine::make(Product::class);
});

it('has a sorts key', function () {
    expect($this->test)
        ->getSortsKey()->toBe(config('refine.config.sorts'))
        ->sortsKey('test')->toBe($this->test)
        ->getSortsKey()->toBe('test');
});

it('has a searches key', function () {
    expect($this->test)
        ->getSearchesKey()->toBe(config('refine.config.searches'))
        ->searchesKey('test')->toBe($this->test)
        ->getSearchesKey()->toBe('test');
});

it('can match', function () {
    expect($this->test)
        ->canMatch()->toBe(config('refine.matches'));

    expect($this->test->match())->toBe($this->test)
        ->canMatch()->toBeTrue();
});

it('has a delimiter', function () {
    expect($this->test)
        ->getDelimiter()->toBe(config('refine.delimiter'))
        ->delimiter('|')->toBe($this->test)
        ->getDelimiter()->toBe('|');
});