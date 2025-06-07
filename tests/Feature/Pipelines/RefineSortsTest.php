<?php

declare(strict_types=1);

use Honed\Refine\Pipelines\RefineSorts;
use Honed\Refine\Refine;
use Honed\Refine\Sort;
use Illuminate\Support\Facades\Request;
use Workbench\App\Models\Product;

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

    expect($this->refine->getResource()->getQuery()->wheres)
        ->toBeEmpty();
});

it('refines default', function () {
    $request = Request::create('/', 'GET', [
        'invalid' => 'test',
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getResource();

    expect($builder->getQuery()->orders)
        ->toBeOnlyOrder('name', 'asc');
});

it('refines', function () {
    $request = Request::create('/', 'GET', [
        'sort' => 'price',
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getResource();

    expect($builder->getQuery()->orders)
        ->toBeOnlyOrder('price', 'asc');
});

it('refines directionally', function () {
    $request = Request::create('/', 'GET', [
        'sort' => '-price',
    ]);

    $this->refine->request($request);

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getResource();

    expect($builder->getQuery()->orders)
        ->toBeOnlyOrder('price', 'desc');
});

it('disables', function () {
    $request = Request::create('/', 'GET', [
        'sort' => 'price',
    ]);

    $this->refine->request($request)->disableSorting();

    $this->pipe->__invoke($this->refine, $this->closure);

    $builder = $this->refine->getResource();

    expect($builder->getQuery()->orders)
        ->toBeEmpty();
});

describe('scope', function () {
    beforeEach(function () {
        $this->refine = $this->refine->scope('scope');
    });

    it('refines default', function () {
        $request = Request::create('/', 'GET', [
            'sort' => 'price',
        ]);

        $this->refine->request($request);

        $this->pipe->__invoke($this->refine, $this->closure);

        $builder = $this->refine->getResource();

        expect($builder->getQuery()->orders)
            ->toBeOnlyOrder('name', 'asc');
    });

    it('refines', function () {
        $request = Request::create('/', 'GET', [
            $this->refine->formatScope('sort') => 'price',
        ]);

        $this->refine->request($request);

        $this->pipe->__invoke($this->refine, $this->closure);

        $builder = $this->refine->getResource();

        expect($builder->getQuery()->orders)
            ->toBeOnlyOrder('price', 'asc');
    });
});
