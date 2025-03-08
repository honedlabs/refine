<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Search;
use Honed\Refine\Sort;
use Honed\Refine\Filter;
use Honed\Refine\Tests\Fixtures\RefineFixture;
use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Tests\Stubs\Status;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();

    $this->refiners = [
        Filter::make('name')->like(),

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
            ->asTime()
            ->operator('>='),

        Filter::make('created_at', 'Newest')
            ->alias('newest')
            ->asTime()
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

        config('refine.config.sorts') => '-price',
        config('refine.config.searches') => $this->term,
    ]);
});

it('refines all', function () {
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
            ->sequence(
                // Order should be search -> filter -> sort
                fn ($search) => $search
                    ->{'type'}->toBe('raw')
                    ->{'sql'}->toBe("LOWER({$this->builder->qualifyColumn('name')}) LIKE ?")
                    ->{'boolean'}->toBe('and'),
                fn ($search) => $search
                    ->{'type'}->toBe('raw')
                    ->{'sql'}->toBe("LOWER({$this->builder->qualifyColumn('description')}) LIKE ?")
                    ->{'boolean'}->toBe('or'),
                fn ($filter) => $filter
                    ->{'type'}->toBe('raw')
                    ->{'boolean'}->toBe('and'),
                fn ($filter) => $filter
                    ->{'type'}->toBe('Basic')
                    ->{'column'}->toBe($this->builder->qualifyColumn('price'))
                    ->{'operator'}->toBe(Filter::LessThan)
                    ->{'value'}->toBe(100)
                    ->{'boolean'}->toBe('and'),
                fn ($filter) => $filter
                    ->{'type'}->toBe('In')
                    ->{'column'}->toBe($this->builder->qualifyColumn('status'))
                    ->{'values'}->toBe([Status::Available->value, Status::Unavailable->value])
                    ->{'boolean'}->toBe('and'),
                fn ($filter) => $filter
                    ->{'type'}->toBe('Basic')
                    ->{'column'}->toBe($this->builder->qualifyColumn('status'))
                    ->{'operator'}->toBe('=')
                    ->{'value'}->toBe(Status::ComingSoon->value)
                    ->{'boolean'}->toBe('and'),
                fn ($filter) => $filter
                    ->{'type'}->toBe('Basic')
                    ->{'column'}->toBe($this->builder->qualifyColumn('best_seller'))
                    ->{'operator'}->toBe('=')
                    ->{'value'}->toBe(true)
                    ->{'boolean'}->toBe('and'),
                fn ($filter) => $filter
                    ->{'type'}->toBe('Date')
                    ->{'column'}->toBe($this->builder->qualifyColumn('created_at'))
                    ->{'operator'}->toBe(Filter::GreaterThan)
                    ->{'value'}->toBe('2000-01-01')
                    ->{'boolean'}->toBe('and'),
                fn ($filter) => $filter
                    ->{'type'}->toBe('Date')
                    ->{'column'}->toBe($this->builder->qualifyColumn('created_at'))
                    ->{'operator'}->toBe(Filter::LessThan)
                    ->{'value'}->toBe('2001-01-01')
                    ->{'boolean'}->toBe('and'),
            )
        )->orders->scoped(fn ($orders) => $orders
            ->toHaveCount(1)
            ->{0}->scoped(fn ($order) => $order
                ->{'column'}->toBe($this->builder->qualifyColumn('price'))
                ->{'direction'}->toBe('desc')
            )
        );
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
        ->sequence(
            // Order should be search -> filter -> sort
            fn ($search) => $search
                ->{'type'}->toBe('raw')
                ->{'sql'}->toBe("LOWER({$this->builder->qualifyColumn('name')}) LIKE ?")
                ->{'boolean'}->toBe('and'),
            fn ($search) => $search
                ->{'type'}->toBe('raw')
                ->{'sql'}->toBe("LOWER({$this->builder->qualifyColumn('description')}) LIKE ?")
                ->{'boolean'}->toBe('or'),
            fn ($filter) => $filter
                ->{'type'}->toBe('raw')
                ->{'boolean'}->toBe('and'),
            fn ($filter) => $filter
                ->{'type'}->toBe('Basic')
                ->{'column'}->toBe($this->builder->qualifyColumn('price'))
                ->{'operator'}->toBe(Filter::LessThan)
                ->{'value'}->toBe(100)
                ->{'boolean'}->toBe('and'),
            fn ($filter) => $filter
                ->{'type'}->toBe('In')
                ->{'column'}->toBe($this->builder->qualifyColumn('status'))
                ->{'values'}->toBe([Status::Available->value, Status::Unavailable->value])
                ->{'boolean'}->toBe('and'),
            fn ($filter) => $filter
                ->{'type'}->toBe('Basic')
                ->{'column'}->toBe($this->builder->qualifyColumn('status'))
                ->{'operator'}->toBe('=')
                ->{'value'}->toBe(Status::ComingSoon->value)
                ->{'boolean'}->toBe('and'),
            fn ($filter) => $filter
                ->{'type'}->toBe('Basic')
                ->{'column'}->toBe($this->builder->qualifyColumn('best_seller'))
                ->{'operator'}->toBe('=')
                ->{'value'}->toBe(true)
                ->{'boolean'}->toBe('and'),
            fn ($filter) => $filter
                ->{'type'}->toBe('Date')
                ->{'column'}->toBe($this->builder->qualifyColumn('created_at'))
                ->{'operator'}->toBe(Filter::GreaterThan)
                ->{'value'}->toBe('2000-01-01')
                ->{'boolean'}->toBe('and'),
            fn ($filter) => $filter
                ->{'type'}->toBe('Date')
                ->{'column'}->toBe($this->builder->qualifyColumn('created_at'))
                ->{'operator'}->toBe(Filter::LessThan)
                ->{'value'}->toBe('2001-01-01')
                ->{'boolean'}->toBe('and'),
        )
        )->orders->scoped(fn ($orders) => $orders
        ->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'column'}->toBe($this->builder->qualifyColumn('price'))
        ->{'direction'}->toBe('desc')
        )
        );
});

it('requires refiners to be set', function () {
    expect(Refine::make(Product::class)->using($this->refiners)->getQuery())
        ->wheres->scoped(fn ($wheres) => $wheres
            ->toBeArray()
            ->toBeEmpty()
        )->orders->scoped(fn ($orders) => $orders
            ->toBeArray()
            ->toHaveCount(1)
            ->{0}->scoped(fn ($order) => $order
                ->toBeArray()
                ->{'column'}->toBe(Product::query()->qualifyColumn('name'))
                ->{'direction'}->toBe('desc')
            )
        );
});

it('can change the search columns', function () {
    $request = Request::create('/', 'GET', [
        config('refine.config.searches') => 'search+term',
        config('refine.config.matches') => 'description',
    ]);

    Refine::make($this->builder)
        ->using($this->refiners)
        ->request($request)
        ->match()
        ->refine();

    expect($this->builder->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(1)
        ->toEqualCanonicalizing([
            [
                'type' => 'raw',
                'sql' => "LOWER({$this->builder->qualifyColumn('description')}) LIKE ?",
                'boolean' => 'and',
            ]
        ]);

});

it('throws exception when no builder is set', function () {
    Refine::make('non-existent-class')->using($this->refiners)->getQuery();
})->throws(\InvalidArgumentException::class);

it('has array representation', function () {
    $refine = Refine::make(Product::class)
        ->request($this->request)
        ->using($this->refiners);

    expect($refine->refine()->toArray())
        ->toHaveKeys(['sorts', 'filters', 'config'])
        ->{'config'}->scoped(fn ($config) => $config
            ->{'delimiter'}->toBe(config('refine.delimiter'))
            ->{'search'}->toBe($this->term)
            ->{'searches'}->toBe(config('refine.config.searches'))
            ->{'sorts'}->toBe(config('refine.config.sorts'))
        );

    expect($refine->match()->refine()->toArray())
        ->toHaveKeys(['sorts', 'filters', 'searches', 'config'])
        ->{'searches'}->toHaveCount(2)
        ->{'config'}->scoped(fn ($config) => $config
            ->{'delimiter'}->toBe(config('refine.delimiter'))
            ->{'search'}->toBe($this->term)
            ->{'searches'}->toBe(config('refine.config.searches'))
            ->{'sorts'}->toBe(config('refine.config.sorts'))
            ->{'matches'}->toBe(config('refine.config.matches'))
        );
});

it('only refines once', function () {
    $refine = Refine::make(Product::class)->using($this->refiners);

    expect($refine)
        ->get()->toBeInstanceOf(Collection::class);
    expect($refine)
        ->paginate()->toBeInstanceOf(LengthAwarePaginator::class);
});