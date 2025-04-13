<?php

declare(strict_types=1);

use Honed\Refine\Filter;
use Honed\Refine\Pipelines\RefineFilters;
use Honed\Refine\Refine;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->pipe = new RefineFilters;
    $this->closure = fn ($refine) => $refine;

    $filters = [
        Filter::make('price')->int(),
    ];

    $this->refine = Refine::make($this->builder)
        ->filters($filters);

});

it('does not refine', function () {
    $request = Request::create('/', 'GET', [
        'invalid' => 'test',
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    expect($this->refine->getResource()->getQuery()->wheres)
        ->toBeEmpty();
});

it('refines', function () {
    $request = Request::create('/', 'GET', [
        'price' => 100,
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getResource();

    expect($builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn('price'), 100);
});

it('disables', function () {
    $request = Request::create('/', 'GET', [
        'price' => 100,
    ]);

    $this->refine->request($request)->exceptFilters();

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getResource();

    expect($builder->getQuery()->wheres)
        ->toBeEmpty();
});

describe('scope', function () {
    beforeEach(function () {
        $this->refine = $this->refine->scope('scope');
    });

    it('does not refine', function () {
        $request = Request::create('/', 'GET', [
            'price' => 100,
        ]);

        $this->refine->request($request);

        $this->pipe->__invoke($this->refine, $this->closure);

        $builder = $this->refine->getResource();

        expect($builder->getQuery()->wheres)
            ->toBeEmpty();
    });

    it('refines', function () {
        $request = Request::create('/', 'GET', [
            $this->refine->formatScope('price') => 100,
        ]);

        $this->refine->request($request);

        $this->pipe->__invoke($this->refine, $this->closure);

        $builder = $this->refine->getResource();

        expect($builder->getQuery()->wheres)
            ->toBeOnlyWhere($this->builder->qualifyColumn('price'), 100);
    });
});
