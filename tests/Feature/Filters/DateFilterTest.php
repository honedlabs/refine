<?php

declare(strict_types=1);

use Honed\Refine\Filters\DateFilter;
use Honed\Refine\Filters\Filter;

beforeEach(function () {
    $this->filter = DateFilter::make('created_at');
});

it('creates', function () {
    expect($this->filter)
        ->getType()->toBe(Filter::DATE)
        ->interpretsAs()->toBe('date');
});
