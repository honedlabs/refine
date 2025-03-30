<?php

declare(strict_types=1);

use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Pipelines\RefineSorts;
use Honed\Refine\Tests\Fixtures\RefineSortsFixture;
use Honed\Refine\Refine;
use Honed\Refine\Sort;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->pipe = new RefineSorts();
    $this->closure = fn ($refine) => $refine;

    $sorts = [
        Sort::make('name')->default(),
        Sort::make('price'),
    ];

    $this->refine = Refine::make($this->builder)
        ->withSorts($sorts);
});

it('does not refine', function () {
    $this->pipe->__invoke($this->refine, $this->closure);

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();
});

it('refines default', function () {
    $request = Request::create('/', 'GET', [
        'invalid' => 'test'
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getBuilder();

    expect($builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn('name'), 'asc');
});

it('refines', function () {
    $request = Request::create('/', 'GET', [
        config('refine.sort_key') => 'price'
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getBuilder();

    expect($builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn('price'), 'asc');
});

it('refines directionally', function () {
    $request = Request::create('/', 'GET', [
        config('refine.sort_key') => '-price'
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getBuilder();

    expect($builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn('price'), 'desc');
});

it('disables', function () {
    $request = Request::create('/', 'GET', [
        config('refine.sort_key') => 'price'
    ]);

    $this->refine->request($request)->withoutSorts();

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getBuilder();

    expect($builder->getQuery()->orders)
        ->toBeEmpty();
});

describe('scope', function () {
    beforeEach(function () {
        $this->refine = $this->refine->scope('scope');
    });

    it('refines default', function () {
        $request = Request::create('/', 'GET', [
            config('refine.sort_key') => 'price'
        ]);

        $this->refine->request($request);

        $this->pipe->__invoke($this->refine, $this->closure);

        $builder = $this->refine->getBuilder();

        expect($builder->getQuery()->orders)
            ->toBeOnlyOrder($this->builder->qualifyColumn('name'), 'asc');
    });

    it('refines', function () {
        $request = Request::create('/', 'GET', [
            $this->refine->formatScope(config('refine.sort_key')) => 'price'
        ]);

        $this->refine->request($request);

        $this->pipe->__invoke($this->refine, $this->closure);

        $builder = $this->refine->getBuilder();

        expect($builder->getQuery()->orders)
            ->toBeOnlyOrder($this->builder->qualifyColumn('price'), 'asc');
    }); 
});