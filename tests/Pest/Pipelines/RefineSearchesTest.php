<?php

declare(strict_types=1);

use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Pipelines\RefineSearches;
use Honed\Refine\Refine;
use Honed\Refine\Search;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->pipe = new RefineSearches();
    $this->closure = fn ($refine) => $refine;

    $searches = [
        Search::make('name'),
        Search::make('description'),
    ];

    $this->refine = Refine::make($this->builder)
        ->withSearches($searches);
});

it('does not refine', function () {
    $request = Request::create('/', 'GET', [
        'invalid' => 'test'
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->refine->getTerm())
        ->toBeNull();
});

it('refines', function () {
    $request = Request::create('/', 'GET', [
        config('refine.search_key') => 'search+value'
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getBuilder();

    expect($builder->getQuery()->wheres)
        ->{0}->toBeSearch($builder->qualifyColumn('name'), 'and')
        ->{1}->toBeSearch($builder->qualifyColumn('description'), 'or');

    expect($this->refine->getTerm())
        ->toBe('search value');
});

it('disables', function () {
    $request = Request::create('/', 'GET', [
        config('refine.search_key') => 'search+value'
    ]);

    $this->refine->request($request)->withoutSearches();

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getBuilder();

    expect($builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->refine->getTerm())
        ->toBe('search value');
});

it('refines with match', function () {
    $request = Request::create('/', 'GET', [
        config('refine.search_key') => 'search+value',
        config('refine.match_key') => 'name'
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getBuilder();

    expect($builder->getQuery()->wheres)
        ->toBeOnlySearch($builder->qualifyColumn('name'));

    expect($this->refine->getTerm())
        ->toBe('search value');
});

describe('scope', function () {
    beforeEach(function () {
        $this->refine = $this->refine->scope('scope');
    });

    it('does not refine', function () {
        $request = Request::create('/', 'GET', [
            config('refine.search_key') => 'search+value',
            config('refine.match_key') => 'description'
        ]);

        $this->refine->request($request);

        $this->pipe->__invoke($this->refine, $this->closure);

        $builder = $this->refine->getBuilder();

        expect($builder->getQuery()->wheres)
            ->toBeEmpty();

        expect($this->refine->getTerm())
            ->toBeNull();
    });

    it('refines', function () {
        $request = Request::create('/', 'GET', [
            $this->refine->formatScope(config('refine.search_key')) => 'search+value',
            $this->refine->formatScope(config('refine.match_key')) => 'description'
        ]);

        $this->refine->request($request);

        $this->pipe->__invoke($this->refine, $this->closure);

        $builder = $this->refine->getBuilder();

        expect($builder->getQuery()->wheres)
            ->toBeOnlySearch($builder->qualifyColumn('description'));

        expect($this->refine->getTerm())
            ->toBe('search value');
    }); 
});