<?php

declare(strict_types=1);

use Honed\Refine\Pipelines\RefineSearches;
use Honed\Refine\Refine;
use Honed\Refine\Search;
use Illuminate\Http\Request;
use Workbench\App\Models\Product;

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
        'invalid' => 'test',
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    expect($this->refine->getResource()->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->refine->getTerm())
        ->toBeNull();
});

it('refines', function () {
    $request = Request::create('/', 'GET', [
        'search' => 'search+value',
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getResource();

    expect($builder->getQuery()->wheres)
        ->{0}->toBeSearch('name', 'and')
        ->{1}->toBeSearch('description', 'or');

    expect($this->refine->getTerm())
        ->toBe('search value');
});

it('disables', function () {
    $request = Request::create('/', 'GET', [
        'search' => 'search+value',
    ]);

    $this->refine->request($request)->disableSearching();

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getResource();

    expect($builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->refine->getTerm())
        ->toBe('search value');
});

it('refines with match', function () {
    $request = Request::create('/', 'GET', [
        'search' => 'search+value',
        'match' => 'name',
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getResource();

    expect($builder->getQuery()->wheres)
        ->toBeOnlySearch('name');

    expect($this->refine->getTerm())
        ->toBe('search value');
});

describe('scope', function () {
    beforeEach(function () {
        $this->refine = $this->refine->scope('scope');
    });

    it('does not refine', function () {
        $request = Request::create('/', 'GET', [
            'search' => 'search+value',
            'match' => 'description',
        ]);

        $this->refine->request($request);

        $this->pipe->__invoke($this->refine, $this->closure);

        $builder = $this->refine->getResource();

        expect($builder->getQuery()->wheres)
            ->toBeEmpty();

        expect($this->refine->getTerm())
            ->toBeNull();
    });

    it('refines', function () {
        $request = Request::create('/', 'GET', [
            $this->refine->formatScope('search') => 'search+value',
            $this->refine->formatScope('match') => 'description',
        ]);

        $this->refine->request($request);

        $this->pipe->__invoke($this->refine, $this->closure);

        $builder = $this->refine->getResource();

        expect($builder->getQuery()->wheres)
            ->toBeOnlySearch('description');

        expect($this->refine->getTerm())
            ->toBe('search value');
    });
});
