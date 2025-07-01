<?php

declare(strict_types=1);

use Honed\Refine\Data\SearchData;
use Honed\Refine\Pipes\SearchQuery;
use Honed\Refine\Refine;
use Honed\Refine\Searches\Search;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->pipe = new SearchQuery();

    $this->query = 'search+value';

    $this->term = Str::of($this->query)
        ->replace('+', ' ')
        ->trim()
        ->toString();

    $this->match = 'name';

    $this->refine = Refine::make(Product::class)
        ->searches([
            Search::make('name'),
            Search::make('description'),
        ]);
});

it('fails', function ($refine) {
    $this->pipe->instance($refine);

    $this->pipe->run();

    expect($refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();
})->with([
    'no search key' => function () {
        $request = Request::create('/', 'GET', [
            'invalid' => $this->query,
        ]);

        return $this->refine->request($request);
    },

    'disabled' => function () {
        $request = Request::create('/', 'GET', [
            $this->refine->getSearchKey() => $this->query,
        ]);

        return $this->refine->notSearchable()->request($request);
    },

    'scope' => function () {
        $request = Request::create('/', 'GET', [
            $this->refine->getSearchKey() => $this->query,
        ]);

        return $this->refine->scope('scope')->request($request);
    },

    'session' => function () {
        $data = new SearchData($this->term, [$this->match]);

        Session::put($this->refine->getPersistKey(), [
            $this->refine->getSearchKey() => $data->toArray(),
        ]);

        return $this->refine;
    },

    'cookie' => function () {
        $data = new SearchData($this->term, [$this->match]);

        $request = Request::create('/', 'GET', cookies: [
            $this->refine->getPersistKey() => json_encode([
                $this->refine->getSearchKey() => $data->toArray(),
            ]),
        ]);

        return $this->refine->request($request);
    },
]);

it('passes non-matchable', function ($refine) {
    $this->pipe->instance($refine);

    $this->pipe->run();

    expect($refine->getBuilder()->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(2)
        ->{0}->toBeSearch('name', 'and')
        ->{1}->toBeSearch('description', 'or');

    expect($this->refine)
        ->getSearchTerm()->toBe('search value')
        ->isSearching()->toBeTrue();
})->with([
    'request' => function () {
        $request = Request::create('/', 'GET', [
            $this->refine->getSearchKey() => $this->query,
        ]);

        return $this->refine->request($request);
    },

    'scope' => function () {
        $this->refine->scope('scope');

        $request = Request::create('/', 'GET', [
            $this->refine->getSearchKey() => $this->query,
        ]);

        return $this->refine->request($request);
    },

    'session' => function () {
        $data = new SearchData($this->term, [$this->match]);

        Session::put($this->refine->getPersistKey(), [
            $this->refine->getSearchKey() => $data->toArray(),
        ]);

        return $this->refine->persistSearchInSession();
    },

    'cookie' => function () {
        $data = new SearchData($this->term, [$this->match]);

        $request = Request::create('/', 'GET', cookies: [
            $this->refine->getPersistKey() => json_encode([
                $this->refine->getSearchKey() => $data->toArray(),
            ]),
        ]);

        $this->refine->request($request)->persistSearchInCookie();

        return $this->refine;
    },
]);

it('passes matchable', function ($refine) {
    $this->pipe->instance($refine->matchable());

    $this->pipe->run();

    expect($refine->getBuilder()->getQuery()->wheres)
        ->toBeOnlySearch($this->match);

    expect($refine)
        ->getSearchTerm()->toBe($this->term)
        ->isSearching()->toBeTrue();
})->with([
    'request' => function () {
        $request = Request::create('/', 'GET', [
            $this->refine->getSearchKey() => $this->query,
            $this->refine->getMatchKey() => $this->match,
        ]);

        return $this->refine->request($request);
    },

    'scope' => function () {
        $this->refine->scope('scope');

        $request = Request::create('/', 'GET', [
            $this->refine->getSearchKey() => $this->query,
            $this->refine->getMatchKey() => $this->match,
        ]);

        return $this->refine->request($request);
    },

    'session' => function () {
        $data = new SearchData($this->term, [$this->match]);

        Session::put($this->refine->getPersistKey(), [
            $this->refine->getSearchKey() => $data->toArray(),
        ]);

        return $this->refine->persistSearchInSession();
    },

    'cookie' => function () {
        $data = new SearchData($this->term, [$this->match]);

        $request = Request::create('/', 'GET', cookies: [
            $this->refine->getPersistKey() => json_encode([
                $this->refine->getSearchKey() => $data->toArray(),
            ]),
        ]);

        $this->refine->request($request)->persistSearchInCookie();

        return $this->refine;
    },
]);

it('scouts', function () {
    $request = Request::create('/', 'GET', [
        $this->refine->getSearchKey() => $this->query,
    ]);

    $this->pipe->instance($this->refine->scout()->request($request));

    $this->pipe->run();

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeOnlyWhereIn('products.id', []);

    expect($this->refine)
        ->getSearchTerm()->toBe($this->term)
        ->isSearching()->toBeTrue();
});
