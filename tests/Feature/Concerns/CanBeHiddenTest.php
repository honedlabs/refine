<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;

beforeEach(function () {
    $this->filter = Filter::make('name');
});

it('can be hidden', function () {
    expect($this->filter)
        ->isNotHidden()->toBeTrue()
        ->isHidden()->toBeFalse()
        ->hidden()->toBe($this->filter)
        ->isHidden()->toBeTrue()
        ->notHidden()->toBe($this->filter)
        ->isNotHidden()->toBeTrue();
});
