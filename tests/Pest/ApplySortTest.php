<?php

declare(strict_types=1);

use Honed\Refine\Sort;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->key = config('refine.sorts_key');
});

it('is not active when the params do not match', function () {
    $name = 'name';

    $sort = Sort::make($name);

    $request = Request::create('/', 'GET', [$this->key => 'other']);

    expect($sort->refine($this->builder, $request, $this->key))
        ->toBeFalse();

    expect($this->builder->getQuery()->orders)
        ->toBeEmpty();

    expect($sort)
        ->isActive()->toBeFalse()
        ->getDirection()->toBeNull()
        ->getNextDirection()->toBe($name);
});

it('uses alias over name', function () {
    $name = 'name';
    $alias = 'alphabetical';

    $sort = Sort::make($name)->alias($alias);

    // It should not be active with name as there is an alias
    $request = Request::create('/', 'GET', [$this->key => $name]);

    expect($sort->refine($this->builder, $request, $this->key))
        ->toBeFalse();

    expect($this->builder->getQuery()->orders)
        ->toBeEmpty();

    expect($sort)
        ->isActive()->toBeFalse()
        ->getDirection()->toBeNull()
        ->getNextDirection()->toBe($alias);

    // Now it should be active
    $request = Request::create('/', 'GET', [$this->key => $alias]);

    expect($sort->refine($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn($name), 'asc');

    expect($sort)
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('asc')
        ->getNextDirection()->toBe('-'.$alias);
});

it('can be singular', function () {
    $name = 'name';
    
    $sort = Sort::make($name)
        ->desc();

    $request = Request::create('/', 'GET', [$this->key => $name]);

    expect($sort->refine($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn($name), 'desc');

    expect($sort)
        ->isSingularDirection()->toBeTrue()
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('desc')
        ->getNextDirection()->toBe($name);
});

it('can invert direction', function () {
    $name = 'name';
    
    $sort = Sort::make($name)
        ->invert();

    $request = Request::create('/', 'GET', [$this->key => '-'.$name]);

    expect($sort->refine($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn($name), 'desc');

    expect($sort)
        ->isInverted()->toBeTrue()
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('desc')
        ->getNextDirection()->toBe($name);
});

it('supports `oldest`, `latest` clauses', function () {
    $name = 'created_at';
    
    $sort = Sort::make($name)
        ->latest(':column');

    $request = Request::create('/', 'GET', [$this->key => $name]);

    expect($sort->refine($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($name, 'desc');

    // expect($sort)
    //     ->isSingularDirection()->toBeTrue()
    //     ->isActive()->toBeTrue()
    //     ->getDirection()->toBe('desc')
    //     ->getNextDirection()->toBe($name);
});

it('supports `orderBy` clauses', function () {
    $name = 'created_at';
    
    $sort = Sort::make($name)
        ->orderBy('created_at', ':direction');

    $request = Request::create('/', 'GET', [$this->key => '-'.$name]);

    expect($sort->refine($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($name, 'desc');
});

it('supports closures', function () {
    $name = 'name';

    $sort = Sort::make($name)
        ->using(fn ($builder, $direction) => $builder->orderBy('created_at', $direction));

    $request = Request::create('/', 'GET', [$this->key => '-'.$name]);

    expect($sort->refine($this->builder, $request, $this->key))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder('created_at', 'desc');
    
    expect($sort)
        ->isActive()->toBeTrue();
});