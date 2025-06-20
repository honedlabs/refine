<?php

declare(strict_types=1);

use Honed\Refine\Filters\BooleanFilter;
use Honed\Refine\Filters\Filter;

beforeEach(function () {
    $this->filter = BooleanFilter::make('is_active');
});

it('creates', function () {
    expect($this->filter)
        ->getType()->toBe(Filter::BOOLEAN)
        ->interpretsAs()->toBe('boolean');
});
