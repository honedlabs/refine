<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->refine = Refine::make(User::class);
});

it('has array representation', function () {
    expect($this->refine->refineToArray())->toBeArray()
        ->toHaveKeys([
            '_sort_key',
            '_search_key',
            '_match_key',
            '_delimiter',
            'term',
            'placeholder',
            'sorts',
            'filters',
            'searches',
        ])
        ->{'_sort_key'}->toBe($this->refine->getSortKey())
        ->{'_search_key'}->toBe($this->refine->getSearchKey())
        ->{'_match_key'}->toBeNull()
        ->{'_delimiter'}->toBe($this->refine->getDelimiter())
        ->{'term'}->toBeNull()
        ->{'placeholder'}->toBeNull()
        ->{'sorts'}->toBe($this->refine->sortsToArray())
        ->{'filters'}->toBe($this->refine->filtersToArray())
        ->{'searches'}->toBe($this->refine->searchesToArray());
});

it('has array representation with match', function () {
    $this->refine->matchable();

    expect($this->refine->refineToArray())
        ->toBeArray()
        ->toHaveKey('_match_key')
        ->{'_match_key'}->toBe($this->refine->getMatchKey());
});

it('has array representation without search', function () {
    expect($this->refine->notSearchable()->refineToArray())
        ->{'_search_key'}->toBeNull();
});

it('has array representation without sort', function () {
    expect($this->refine->notSortable()->refineToArray())
        ->{'_sort_key'}->toBeNull();
});
