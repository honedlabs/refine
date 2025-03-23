<?php

declare(strict_types=1);

use Honed\Refine\AscSort;

it('has asc sort', function () {
    expect(AscSort::make('created_at'))
        ->toBeInstanceOf(AscSort::class)
        ->isFixed()->toBeTrue()
        ->getDirection()->toBe('asc')
        ->getType()->toBe('asc');
});