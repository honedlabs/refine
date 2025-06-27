<?php

declare(strict_types=1);

use Honed\Refine\Searches\Search;

beforeEach(function () {
    $this->search = Search::make('name');
});
