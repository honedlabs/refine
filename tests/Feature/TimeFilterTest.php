<?php

declare(strict_types=1);

use Honed\Refine\TimeFilter;

it('has time filter', function () {
    expect(TimeFilter::make('time'))
        ->toBeInstanceOf(TimeFilter::class)
        ->getType()->toBe('time')
        ->interpretsAs()->toBe('time');
});
