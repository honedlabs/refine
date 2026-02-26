<?php

declare(strict_types=1);

use Honed\Refine\Enums\SearchMode;
use Honed\Refine\Searches\Search;

beforeEach(function () {
    $this->search = Search::make('name');
});

it('can be full text', function () {
    expect($this->search)
        ->getSearchMode()->toBe(SearchMode::Wildcard)
        ->fullText()->toBe($this->search)
        ->getSearchMode()->toBe(SearchMode::NaturalLanguage)
        ->boolean()->toBe($this->search)
        ->getSearchMode()->toBe(SearchMode::Boolean)
        ->wildcard()->toBe($this->search)
        ->getSearchMode()->toBe(SearchMode::Wildcard)
        ->startsWith()->toBe($this->search)
        ->getSearchMode()->toBe(SearchMode::StartsWith)
        ->endsWith()->toBe($this->search)
        ->getSearchMode()->toBe(SearchMode::EndsWith);
});
