<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;
use Honed\Refine\Filters\SelectFilter;
use Honed\Refine\Pipes\FilterQuery;
use Honed\Refine\Refine;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Workbench\App\Enums\Status;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->pipe = new FilterQuery();

    $this->refine = Refine::make(Product::class)
        ->filters([
            Filter::make('price')->int(),
            Filter::make('best_seller')->boolean(),
        ]);
});

it('fails', function (Refine $refine) {
    $this->pipe->instance($refine);

    $this->pipe->run();

    expect($refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();
})->with([
    'no filter key' => function () {
        $request = Request::create('/', 'GET', [
            'invalid' => 100,
        ]);

        return $this->refine->request($request);
    },

    'disabled' => function () {
        $request = Request::create('/', 'GET', [
            'price' => 100,
        ]);

        return $this->refine->notFilterable()->request($request);
    },

    'scope' => function () {
        $request = Request::create('/', 'GET', [
            'price' => 100,
        ]);

        return $this->refine->scope('scope')->request($request);
    },

    'session' => function () {
        Session::put($this->refine->getPersistKey(), [
            'price' => 100,
        ]);

        return $this->refine;
    },

    'cookie' => function () {
        $request = Request::create('/', 'GET', cookies: [
            $this->refine->getPersistKey() => json_encode([
                'price' => 100,
            ]),
        ]);

        $this->refine->request($request);

        return $this->refine;
    },
]);

it('passes', function (Refine $refine, string $name, mixed $value) {
    $this->pipe->instance($refine);

    $this->pipe->run();

    expect($refine->getBuilder()->getQuery()->wheres)
        ->{is_array($value) ? 'toBeOnlyWhereIn' : 'toBeOnlyWhere'}($name, $value);
})->with([
    'request' => function () {
        $request = Request::create('/', 'GET', [
            'price' => 100,
        ]);

        return [$this->refine->request($request), 'price', 100];
    },
    'defaults' => function () {
        $this->refine->filter(Filter::make('name')->default('joshua'));

        return [$this->refine, 'name', 'joshua'];
    },
    'array defaults' => function () {
        $this->refine->filter(SelectFilter::make('status')->default([Status::Available]));

        return [$this->refine, 'status', [Status::Available]];
    },
    'scope' => function () {
        $this->refine->scope('scope');

        $request = Request::create('/', 'GET', [
            $this->refine->scoped('price') => 100,
        ]);

        return [$this->refine->request($request), 'price', 100];
    },
    'session' => function () {
        Session::put($this->refine->getPersistKey(), [
            'price' => 100,
        ]);

        return [$this->refine->persistFilterInSession(), 'price', 100];
    },
    'cookie' => function () {
        $request = Request::create('/', 'GET', cookies: [
            $this->refine->getPersistKey() => json_encode([
                'price' => 100,
            ]),
        ]);

        $this->refine->request($request)->persistFilterInCookie();

        return [$this->refine, 'price', 100];
    },
    'uses request over store' => function () {
        Session::put($this->refine->getPersistKey(), [
            'price' => 100,
        ]);

        $request = Request::create('/', 'GET', [
            'best_seller' => true,
        ]);

        $this->refine->request($request)->persistFilterInSession();

        return [$this->refine, 'best_seller', true];
    },
]);
