<?php

declare(strict_types=1);

use Honed\Refine\Sorts\Sort;

beforeEach(function () {
    $this->refiner = Sort::make('name');
});

it('can be nullable', function () {
    expect($this->refiner)
        ->isNullable()->toBeFalse()
        ->isNotNullable()->toBeTrue()
        ->nullable()->toBe($this->refiner)
        ->isNullable()->toBeTrue()
        ->notNullable()->toBe($this->refiner)
        ->isNotNullable()->toBeTrue();
});
