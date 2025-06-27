<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Stores\CookieStore;
use Honed\Refine\Stores\SessionStore;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->refine = Refine::make(User::class);
});

it('has search store', function () {
    expect($this->refine)
        ->getSearchStore()->toBeNull()
        ->shouldPersistSearch()->toBeFalse()

        ->persistSearchInSession()->toBe($this->refine)
        ->getSearchStore()->toBeInstanceOf(SessionStore::class)
        ->shouldPersistSearch()->toBeTrue()

        ->persistSearchInCookie()->toBe($this->refine)
        ->getSearchStore()->toBeInstanceOf(CookieStore::class)
        ->shouldPersistSearch()->toBeTrue()

        ->persistSearch()->toBe($this->refine)
        ->getSearchStore()->toBeInstanceOf(SessionStore::class)
        ->shouldPersistSearch()->toBeTrue();
});

it('has filter store', function () {
    expect($this->refine)
        ->getFilterStore()->toBeNull()
        ->shouldPersistFilter()->toBeFalse()

        ->persistFilterInSession()->toBe($this->refine)
        ->getFilterStore()->toBeInstanceOf(SessionStore::class)
        ->shouldPersistFilter()->toBeTrue()

        ->persistFilterInCookie()->toBe($this->refine)
        ->getFilterStore()->toBeInstanceOf(CookieStore::class)
        ->shouldPersistFilter()->toBeTrue()

        ->persistFilter()->toBe($this->refine)
        ->getFilterStore()->toBeInstanceOf(SessionStore::class)
        ->shouldPersistFilter()->toBeTrue();
});

it('has sort store', function () {
    expect($this->refine)
        ->getSortStore()->toBeNull()
        ->shouldPersistSort()->toBeFalse()

        ->persistSortInSession()->toBe($this->refine)
        ->getSortStore()->toBeInstanceOf(SessionStore::class)
        ->shouldPersistSort()->toBeTrue()

        ->persistSortInCookie()->toBe($this->refine)
        ->getSortStore()->toBeInstanceOf(CookieStore::class)
        ->shouldPersistSort()->toBeTrue()

        ->persistSort()->toBe($this->refine)
        ->getSortStore()->toBeInstanceOf(SessionStore::class)
        ->shouldPersistSort()->toBeTrue();
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
