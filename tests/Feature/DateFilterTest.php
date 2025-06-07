<?php

declare(strict_types=1);

use Honed\Refine\DateFilter;

it('has date filter', function () {
    expect(DateFilter::make('created_at'))
        ->toBeInstanceOf(DateFilter::class)
        ->getType()->toBe('date')
        ->interpretsAs()->toBe('date');
});
