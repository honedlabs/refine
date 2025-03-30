<?php

declare(strict_types=1);

use Honed\Refine\Concerns\HasQualifier;

beforeEach(function () {
    $this->test = new class {
        use HasQualifier;
    };
});

it('has qualifier', function () {
    expect($this->test)
        ->isQualified()->toBeTrue()
        ->unqualify()->toBe($this->test)
        ->isQualified()->toBeFalse()
        ->qualify()->toBe($this->test)
        ->isQualified()->toBeTrue();
});