<?php

declare(strict_types=1);

use Honed\Refine\NumberFilter;

it('has number filter', function () {
    expect(NumberFilter::make('price'))
        ->toBeInstanceOf(NumberFilter::class)
        ->getType()->toBe('number')
        ->interpretsAs()->toBe('int');
});