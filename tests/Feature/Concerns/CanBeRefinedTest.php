<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Stores\CookieStore;
use Honed\Refine\Stores\SessionStore;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->refine = Refine::make(User::class);
});

it('has a before callback', function () {
    expect($this->refine)
        ->getBeforeCallback()->toBeNull()
        ->before(fn () => 'before')->toBe($this->refine)
        ->getBeforeCallback()->toBeInstanceOf(Closure::class);
});

it('has an after callback', function () {
    expect($this->refine)
        ->getAfterCallback()->toBeNull()
        ->after(fn () => 'after')->toBe($this->refine)
        ->getAfterCallback()->toBeInstanceOf(Closure::class);
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

it('sets persistent store', function () {
    expect($this->refine)
        ->persistent()->toBe($this->refine);
})->todo();

it('has array representation', function () {
    expect($this->refine->toArray())->toBeArray()
        ->toHaveKeys([
            'sort',
            'search',
            'match',
            'term',
            'delimiter',
            'placeholder',
            'sorts',
            'filters',
            'searches',
        ]);
});
