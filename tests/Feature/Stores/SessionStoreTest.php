<?php

declare(strict_types=1);

use Honed\Refine\Stores\SessionStore;
use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    $this->store = SessionStore::make('session');
});

it('can persist key value pairs to the session', function () {
    expect($this->store)
        ->put('key', 'value')->toBe($this->store);

    $this->store->persist();

    expect(Session::get('session'))->toEqual([
        'key' => 'value',
    ]);
});

it('can persist arrays to the session', function () {
    $this->store->put('key', ['key' => 'value']);

    expect($this->store)
        ->put(['key' => 'value', 'key2' => 'value2'])->toBe($this->store);

    $this->store->persist();

    expect(Session::get('session'))->toEqual([
        'key' => 'value',
        'key2' => 'value2',
    ]);
});

it('can retrieve all data from the session', function () {
    Session::put('session', [
        'key' => 'value',
    ]);

    $store = SessionStore::make('session');

    expect($store->get())->toEqual(['key' => 'value']);
});

it('can retrieve data from the session', function () {
    Session::put('session', [
        'key' => 'value',
    ]);

    $store = SessionStore::make('session');

    expect($store->get('key'))->toEqual('value');
});

it('retrieves null when the key does not exist', function () {
    expect($this->store)
        ->get('key')->toBeNull();
});

it('can set the session', function () {
    $session = App::make(SessionContract::class);

    expect($this->store)
        ->session($session)->toBe($this->store);
});
