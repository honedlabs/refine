<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;
use Honed\Refine\Pipes\FilterQuery;
use Honed\Refine\Refine;
use Illuminate\Support\Facades\Request;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->pipe = new FilterQuery();

    $this->name = 'price';

    $this->value = 100;

    $this->refine = Refine::make(User::class)
        ->filters(Filter::make($this->name)->int());
});

it('needs a filter key', function () {
    $request = Request::create('/', 'GET', [
        'invalid' => $this->value,
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();
});

it('applies filter', function () {
    $request = Request::create('/', 'GET', [
        $this->name => $this->value,
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeOnlyWhere($this->name, $this->value);
});

it('applies filter with default', function () {
    $name = 'name';
    $value = 'joshua';

    $request = Request::create('/', 'GET');

    $this->pipe->run(
        $this->refine
            ->request($request)
            ->filters(Filter::make($name)->default($value))
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeOnlyWhere($name, $value);
});

it('disables filtering', function () {
    $request = Request::create('/', 'GET', [
        $this->name => $this->value,
    ]);

    $this->pipe->run(
        $this->refine
            ->notFilterable()
            ->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();
});

it('does not apply filter if key is not scoped', function () {
    $request = Request::create('/', 'GET', [
        $this->name => $this->value,
    ]);

    $this->pipe->run(
        $this->refine
            ->scope('scope')
            ->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->orders)
        ->toBeEmpty();
});

it('sorts with scoped key', function () {
    $this->refine->scope('scope');

    $request = Request::create('/', 'GET', [
        $this->refine->formatScope($this->name) => $this->value,
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeOnlyWhere($this->name, $this->value);
});
