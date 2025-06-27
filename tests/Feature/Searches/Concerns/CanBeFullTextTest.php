<?php

declare(strict_types=1);

use Honed\Refine\Searches\Search;

beforeEach(function () {
    $this->search = Search::make('name');
});

it('can be full text', function () {
    expect($this->search)
        ->isFullText()->toBeFalse()
        ->isNotFullText()->toBeTrue()
        ->fullText()->toBe($this->search)
        ->isFullText()->toBeTrue()
        ->notFullText()->toBe($this->search)
        ->isNotFullText()->toBeTrue();
});
