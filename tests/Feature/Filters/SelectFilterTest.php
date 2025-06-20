<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;
use Honed\Refine\Filters\SelectFilter;

beforeEach(function () {
    $this->filter = SelectFilter::make('status');
});

it('creates', function () {
    expect($this->filter)
        ->interpretsAs()->toBe('array')
        ->isMultiple()->toBeTrue()
        ->getType()->toBe(Filter::SELECT);
});
