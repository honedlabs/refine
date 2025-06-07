<?php

declare(strict_types=1);

use Honed\Refine\BooleanFilter;

it('has boolean filter', function () {
    expect(BooleanFilter::make('is_active'))
        ->toBeInstanceOf(BooleanFilter::class)
        ->getType()->toBe('boolean')
        ->interpretsAs()->toBe('boolean');
});
