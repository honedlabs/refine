<?php

declare(strict_types=1);

use Honed\Refine\DatetimeFilter;

it('has datetime filter', function () {
    expect(DatetimeFilter::make('datetime'))
        ->toBeInstanceOf(DatetimeFilter::class)
        ->getType()->toBe('datetime')
        ->interpretsAs()->toBe('datetime');
});
