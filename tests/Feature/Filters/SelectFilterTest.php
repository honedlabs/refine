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

it('has representation', function () {
    expect($this->filter)
        ->toArray()->toEqual([
            'name' => 'status',
            'label' => 'Status',
            'active' => false,
            'type' => 'select',
            'meta' => [],
            'value' => null,
            'options' => [],
            'multiple' => true,
        ]);
});
