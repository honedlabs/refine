<?php

declare(strict_types=1);

use Honed\Refine\Searches\Search;

it('has type', function () {
    expect(Search::make('name'))
        ->getType()->toBe('search');
})->skip();

it('has definition', function () {
    // expect(new NameSearch())

})->todo();
