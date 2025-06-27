<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Stores\CookieStore;
use Honed\Refine\Stores\SessionStore;
use Workbench\App\Models\User;
use Workbench\App\Refiners\RefineUser;

beforeEach(function () {
    $this->refine = Refine::make(User::class);
});

it('sets persist key', function () {
    expect($this->refine)
        ->getPersistKey()->toBe('refine')
        ->persistKey('key')->toBe($this->refine)
        ->getPersistKey()->toBe('key');

    expect(RefineUser::make())
        ->getPersistKey()->toBe('refine-user');
});

it('sets store', function () {
    expect($this->refine)
        ->getStore(true)->toBeInstanceOf(SessionStore::class)
        ->persistInCookie()->toBe($this->refine)
        ->getStore(true)->toBeInstanceOf(CookieStore::class)
        ->persistInSession()->toBe($this->refine)
        ->getStore(true)->toBeInstanceOf(SessionStore::class);
});

it('sets lifetime', function () {
    expect($this->refine)
        ->lifetime(10)->toBe($this->refine);
});

it('gets stores', function () {
    expect($this->refine)
        ->getStore(SessionStore::NAME)->toBeInstanceOf(SessionStore::class)
        ->getStore(CookieStore::NAME)->toBeInstanceOf(CookieStore::class)
        ->getStores()->toHaveKeys([
            SessionStore::NAME,
            CookieStore::NAME,
        ]);
});
