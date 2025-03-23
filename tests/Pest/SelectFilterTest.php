<?php

declare(strict_types=1);

use Honed\Refine\SelectFilter;

it('has text filter', function () {
    expect(SelectFilter::make('status'))
        ->toBeInstanceOf(SelectFilter::class)
        ->interpretsAs()->toBe('array')
        ->isMultiple()->toBeTrue()
        ->getType()->toBe('multiple');
});