<?php

declare(strict_types=1);

use Honed\Refine\Pipes\SearchQuery;
use Honed\Refine\Refine;
use Honed\Refine\Searches\Search;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->pipe = new SearchQuery();

    $this->query = 'search+value';

    $this->term = Str::of($this->query)
        ->replace('+', ' ')
        ->trim()
        ->toString();

    $this->refine = Refine::make(Product::class)
        ->searches([
            Search::make('name'),
            Search::make('description'),
        ]);
});

it('needs a search term', function () {
    $request = Request::create('/', 'GET', [
        'invalid' => $this->query,
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->refine->getTerm())
        ->toBeNull();
});

it('applies search', function () {
    $request = Request::create('/', 'GET', [
        $this->refine->getSearchKey() => $this->query,
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->{0}->toBeSearch('name', 'and')
        ->{1}->toBeSearch('description', 'or');

    expect($this->refine->getTerm())
        ->toBe($this->term);
});

it('applies search without matching', function () {
    $request = Request::create('/', 'GET', [
        $this->refine->getSearchKey() => $this->query,
        $this->refine->getMatchKey() => 'name',
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->{0}->toBeSearch('name', 'and')
        ->{1}->toBeSearch('description', 'or');

    expect($this->refine->getTerm())
        ->toBe($this->term);
});

it('applies search with matching', function () {
    $this->refine->matchable();

    $request = Request::create('/', 'GET', [
        $this->refine->getSearchKey() => $this->query,
        $this->refine->getMatchKey() => 'name',
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeOnlySearch('name');

    expect($this->refine->getTerm())
        ->toBe($this->term);
});

it('disables search', function () {
    $request = Request::create('/', 'GET', [
        $this->refine->getSearchKey() => $this->query,
    ]);

    $this->pipe->run(
        $this->refine
            ->notSearchable()
            ->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->refine->getTerm())
        ->toBe($this->term);
});

it('does not apply search if key is not scoped', function () {
    $request = Request::create('/', 'GET', [
        $this->refine->getSearchKey() => $this->query,
        $this->refine->getMatchKey() => 'name',
    ]);

    $this->pipe->run(
        $this->refine
            ->scope('scope')
            ->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->orders)
        ->toBeEmpty();

    expect($this->refine->getTerm())
        ->toBeNull();
});

it('applies search with scoped key', function () {
    $this->refine->matchable()->scope('scope');

    $request = Request::create('/', 'GET', [
        $this->refine->getSearchKey() => $this->query,
        $this->refine->getMatchKey() => 'description',
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeOnlySearch('description');

    expect($this->refine->getTerm())
        ->toBe($this->term);
});

it('uses cookie persisted search', function () {})->todo();

it('uses session persisted search', function () {})->todo();
