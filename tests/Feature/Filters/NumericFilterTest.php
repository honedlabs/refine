<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;
use Honed\Refine\Filters\NumericFilter;

beforeEach(function () {
    $this->filter = NumericFilter::make('price');
});

it('creates', function () {
    expect($this->filter)
        ->getType()->toBe(Filter::NUMBER)
        ->interpretsAs()->toBe('int');
});
