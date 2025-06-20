<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;
use Honed\Refine\Filters\TimeFilter;

beforeEach(function () {
    $this->filter = TimeFilter::make('time');
});

it('creates', function () {
    expect($this->filter)
        ->getType()->toBe(Filter::TIME)
        ->interpretsAs()->toBe('time');
});
