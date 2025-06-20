<?php

declare(strict_types=1);

use Honed\Refine\Stores\CookieStore;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

beforeEach(function () {
    $request = Request::create('/', cookies: [
        'cookie' => json_encode(['key' => 'value']),
    ]);

    $this->store = CookieStore::make('cookie')
        ->request($request)
        ->resolve();
});

it('can persist key value pairs to a queued cookie', function () {
    $this->store->put('key', 'value');

    $this->store->persist();

    expect(Cookie::getQueuedCookies())
        ->toBeArray()
        ->toHaveCount(1)
        ->{0}->getName()->toEqual('cookie');
});

it('can persist arrays to the cookie', function () {
    $this->store->put('key', ['key' => 'value']);

    expect($this->store)
        ->put(['key' => 'value', 'key2' => 'value2'])->toBe($this->store);

    $this->store->persist();

    expect(Cookie::getQueuedCookies())
        ->toBeArray()
        ->toHaveCount(1)
        ->{0}->getName()->toEqual('cookie');
});

it('can retrieve all data from cookie', function () {
    expect($this->store->get())->toEqual(['key' => 'value']);
});

it('can retrieve data from cookie', function () {
    expect($this->store->get('key'))->toEqual('value');
});

it('retrieves null when the key does not exist', function () {
    expect($this->store->get('invalid'))->toBeNull();
});

it('can set the cookie jar', function () {
    $cookieJar = App::make(CookieJar::class);

    expect($this->store)
        ->cookieJar($cookieJar)->toBe($this->store);
});

it('can set the lifetime of the cookie', function () {
    expect($this->store)
        ->lifetime(10)->toBe($this->store);
});
