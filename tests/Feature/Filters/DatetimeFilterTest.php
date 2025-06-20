<?php

declare(strict_types=1);

use Honed\Refine\Filters\DatetimeFilter;
use Honed\Refine\Filters\Filter;

beforeEach(function () {
    $this->filter = DatetimeFilter::make('datetime');
});

it('creates', function () {
    expect($this->filter)
        ->getType()->toBe(Filter::DATETIME)
        ->interpretsAs()->toBe('datetime');
});
