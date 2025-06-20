<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Feature\Concerns;

use Honed\Refine\Concerns\HasDelimiter;

beforeEach(function () {
    $this->test = new class()
    {
        use HasDelimiter;
    };
});

it('sets', function () {
    expect($this->test)
        ->getDelimiter()->toBe(',')
        ->delimiter('|')->toBe($this->test)
        ->getDelimiter()->toBe('|');
});
