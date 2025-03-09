<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Search;
use Honed\Refine\Sort;
use Honed\Refine\Filter;
use Honed\Refine\Tests\Fixtures\RefineFixture;
use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Tests\Stubs\Status;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();

    $this->refiners = [
        Filter::make('name')
            ->operator('like'),

        Filter::make('price', 'Maximum price')
            ->strict()
            ->operator('>=')
            ->options([10, 20, 50, 100]),

        Filter::make('status')
            ->strict()
            ->enum(Status::class)
            ->multiple(),

        Filter::make('status', 'Single')
            ->alias('only')
            ->enum(Status::class),

        Filter::make('best_seller', 'Favourite')
            ->asBoolean()
            ->alias('favourite'),

        Filter::make('created_at', 'Oldest')
            ->alias('oldest')
            ->asDate()
            ->operator('>='),

        Filter::make('created_at', 'Newest')
            ->alias('newest')
            ->asDate()
            ->operator('<='),

        Sort::make('name', 'A-Z')
            ->alias('name-desc')
            ->desc()
            ->default(),

        Sort::make('name', 'Z-A')
            ->alias('name-asc')
            ->asc(),

        Sort::make('price'),

        Sort::make('best_seller', 'Favourite')
            ->alias('favourite'),

        Search::make('name'),

        Search::make('description'),
    ];

    $this->term = 'search term';

    $this->request = Request::create('/', 'GET', [
        'name' => 'test',
        'price' => 100,
        'status' => \sprintf('%s,%s', Status::Available->value, Status::Unavailable->value),
        'only' => Status::ComingSoon->value,
        'favourite' => '1',
        'oldest' => '2000-01-01',
        'newest' => '2001-01-01',
        config('refine.sorts_key') => '-price',
        config('refine.searches_key') => $this->term,
    ]);
});

it('refines anonymously', function () {
    $refine = Refine::make($this->builder);

    expect($refine)
        ->request($this->request)->toBe($refine)
        ->using($this->refiners)->toBe($refine)
        ->hasSorts()->toBeTrue()
        ->hasFilters()->toBeTrue()
        ->hasSearch()->toBeTrue()
        ->refine()->toBe($refine);

    expect($this->builder->getQuery())
        ->wheres->scoped(fn ($wheres) => $wheres
            ->toBeArray()
            ->toHaveCount(9)
            ->toEqualCanonicalizing([
                [
                    'type' => 'raw',
                    'sql' => "LOWER({$this->builder->qualifyColumn('name')}) LIKE ?",
                    'boolean' => 'and',
                ],
                [
                    'type' => 'raw',
                    'sql' => "LOWER({$this->builder->qualifyColumn('description')}) LIKE ?",
                    'boolean' => 'or',
                ],
                [
                    'type' => 'raw',
                    'sql' => "LOWER({$this->builder->qualifyColumn('name')}) LIKE ?",
                    'boolean' => 'and',
                ],
                [
                    'type' => 'Basic',
                    'column' => $this->builder->qualifyColumn('price'),
                    'operator' => '>=',
                    'value' => 100,
                    'boolean' => 'and',
                ],
                [
                    'type' => 'In',
                    'column' => $this->builder->qualifyColumn('status'),
                    'values' => [Status::Available->value, Status::Unavailable->value],
                    'boolean' => 'and',
                ],
                [
                    'type' => 'Basic',
                    'column' => $this->builder->qualifyColumn('status'),
                    'operator' => '=',
                    'value' => Status::ComingSoon->value,
                    'boolean' => 'and',
                ],
                [
                    'type' => 'Basic',
                    'column' => $this->builder->qualifyColumn('best_seller'),
                    'operator' => '=',
                    'value' => true,
                    'boolean' => 'and',
                ],
                [
                    'type' => 'Date',
                    'column' => $this->builder->qualifyColumn('created_at'),
                    'operator' => '>=',
                    'value' => '2000-01-01',
                    'boolean' => 'and',
                ],
                [
                    'type' => 'Date',
                    'column' => $this->builder->qualifyColumn('created_at'),
                    'operator' => '<=',
                    'value' => '2001-01-01',
                    'boolean' => 'and',
                ],
            ])
        )->orders->toBeOnlyOrder($this->builder->qualifyColumn('price'), 'desc');
});

it('refines all with fixture', function () {
    expect(RefineFixture::make($this->builder))
        ->toBeInstanceOf(RefineFixture::class)
        ->request($this->request)->toBeInstanceOf(RefineFixture::class)
        ->hasSorts()->toBeTrue()
        ->hasFilters()->toBeTrue()
        ->hasSearch()->toBeTrue()
        ->refine()->toBeInstanceOf(RefineFixture::class);

    expect($this->builder->getQuery())
        ->wheres->scoped(fn ($wheres) => $wheres
        ->toBeArray()
        ->toHaveCount(9)
        ->toEqualCanonicalizing([
            [
                'type' => 'raw',
                'sql' => "LOWER({$this->builder->qualifyColumn('name')}) LIKE ?",
                'boolean' => 'and',
            ],
            [
                'type' => 'raw',
                'sql' => "LOWER({$this->builder->qualifyColumn('description')}) LIKE ?",
                'boolean' => 'or',
            ],
            [
                'type' => 'raw',
                'sql' => "LOWER({$this->builder->qualifyColumn('name')}) LIKE ?",
                'boolean' => 'and',
            ],
            [
                'type' => 'Basic',
                'column' => $this->builder->qualifyColumn('price'),
                'operator' => '>=',
                'value' => 100,
                'boolean' => 'and',
            ],
            [
                'type' => 'In',
                'column' => $this->builder->qualifyColumn('status'),
                'values' => [Status::Available->value, Status::Unavailable->value],
                'boolean' => 'and',
            ],
            [
                'type' => 'Basic',
                'column' => $this->builder->qualifyColumn('status'),
                'operator' => '=',
                'value' => Status::ComingSoon->value,
                'boolean' => 'and',
            ],
            [
                'type' => 'Basic',
                'column' => $this->builder->qualifyColumn('best_seller'),
                'operator' => '=',
                'value' => true,
                'boolean' => 'and',
            ],
            [
                'type' => 'Date',
                'column' => $this->builder->qualifyColumn('created_at'),
                'operator' => '>=',
                'value' => '2000-01-01',
                'boolean' => 'and',
            ],
            [
                'type' => 'Date',
                'column' => $this->builder->qualifyColumn('created_at'),
                'operator' => '<=',
                'value' => '2001-01-01',
                'boolean' => 'and',
            ],
        ])
    )->orders->toBeOnlyOrder($this->builder->qualifyColumn('price'), 'desc');
});

it('can select the search columns', function () {
    $request = Request::create('/', 'GET', [
        config('refine.searches_key') => 'search+term',
        config('refine.matches_key') => 'description',
    ]);

    Refine::make($this->builder)
        ->using($this->refiners)
        ->request($request)
        ->match()
        ->refine();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch($this->builder->qualifyColumn('description'));

});