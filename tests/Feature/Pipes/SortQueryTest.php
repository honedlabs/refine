<?php

declare(strict_types=1);

use Honed\Refine\Pipes\SortQuery;
use Honed\Refine\Refine;
use Honed\Refine\Sorts\Sort;
use Illuminate\Support\Facades\Request;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->pipe = new SortQuery();

    $this->name = 'name';

    $this->refine = Refine::make(User::class)
        ->sorts(Sort::make($this->name));
});

it('needs a sort key', function () {
    $request = Request::create('/', 'GET', [
        'invalid' => $this->name,
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->orders)
        ->toBeEmpty();
});

it('applies sort', function () {
    $request = Request::create('/', 'GET', [
        $this->refine->getSortKey() => $this->name,
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->orders)
        ->toBeOnlyOrder($this->name, Sort::ASCENDING);
});

it('applies default sort', function () {
    $name = 'price';

    $request = Request::create('/', 'GET', [
        $this->refine->getSortKey() => $name,
    ]);

    $this->pipe->run(
        $this->refine
            ->sorts(Sort::make($name)->default())
            ->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->orders)
        ->toBeOnlyOrder($name, Sort::ASCENDING);
});

it('applies sort with direction', function () {
    $request = Request::create('/', 'GET', [
        $this->refine->getSortKey() => '-'.$this->name,
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->orders)
        ->toBeOnlyOrder($this->name, Sort::DESCENDING);
});

it('disables sort', function () {
    $request = Request::create('/', 'GET', [
        $this->refine->getSortKey() => $this->name,
    ]);

    $this->pipe->run(
        $this->refine
            ->sortable(false)
            ->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->orders)
        ->toBeEmpty();
});

it('does not apply sort if key is not scoped', function () {
    $request = Request::create('/', 'GET', [
        $this->refine->getSortKey() => $this->name,
    ]);

    $this->pipe->run(
        $this->refine
            ->scope('scope')
            ->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->orders)
        ->toBeEmpty();
});

it('applies sort with scoped key', function () {
    $this->refine->scope('scope');

    $request = Request::create('/', 'GET', [
        $this->refine->getSortKey() => $this->name,
    ]);

    $this->pipe->run(
        $this->refine->request($request)
    );

    expect($this->refine->getBuilder()->getQuery()->orders)
        ->toBeOnlyOrder($this->name, Sort::ASCENDING);
});
