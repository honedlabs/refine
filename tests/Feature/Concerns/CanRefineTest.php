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
            'sort',
            'search',
            'match',
            'term',
            'placeholder',
            'delimiter',
            'sorts',
            'filters',
            'searches',
        ])
        ->{'sort'}->toBe($this->refine->getSortKey())
        ->{'search'}->toBe($this->refine->getSearchKey())
        ->{'term'}->toBeNull()
        ->{'placeholder'}->toBeNull()
        ->{'delimiter'}->toBe($this->refine->getDelimiter())
        ->{'sorts'}->toBe($this->refine->sortsToArray())
        ->{'filters'}->toBe($this->refine->filtersToArray())
        ->{'searches'}->toBe($this->refine->searchesToArray());

    expect($this->refine->refineToArray()['match'])->toBeNull();
});

it('has array representation with match', function () {
    $this->refine->matchable();

    expect($this->refine->refineToArray()['match'])
        ->toBeString()
        ->toBe($this->refine->getMatchKey());
});

it('has array representation without search', function () {
    expect($this->refine->notSearchable()->refineToArray())
        ->{'search'}->toBeNull();
});

it('has array representation without sort', function () {
    expect($this->refine->notSortable()->refineToArray())
        ->{'sort'}->toBeNull();
});
