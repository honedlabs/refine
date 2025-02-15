<?php

declare(strict_types=1);

use Honed\Refine\Filters\BooleanFilter;
use Honed\Refine\Filters\DateFilter;
use Honed\Refine\Filters\Filter;
use Honed\Refine\Filters\SetFilter;
use Honed\Refine\Refine;
use Honed\Refine\Searches\Search;
use Honed\Refine\Sorts\Sort;
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

        SetFilter::make('price', 'Maximum price')
            ->options([10, 20, 50, 100])->lt(),

        SetFilter::make('status')
            ->enum(Status::class)
            ->multiple(),

        SetFilter::make('status', 'Single')
            ->alias('only')
            ->enum(Status::class),

        BooleanFilter::make('best_seller', 'Favourite')
            ->alias('favourite'),

        DateFilter::make('created_at', 'Oldest')
            ->alias('oldest')
            ->gt(),

        DateFilter::make('created_at', 'Newest')
            ->alias('newest')
            ->lt(),

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

    $this->request = Request::create('/', 'GET', [
        'name' => 'test',

        'price' => 100,
        'status' => \sprintf('%s,%s', Status::Available->value, Status::Unavailable->value),
        'only' => Status::ComingSoon->value,

        'favourite' => '1',

        'oldest' => '2000-01-01',
        'newest' => '2001-01-01',

        config('refine.keys.sorts') => '-price',
        config('refine.keys.searches') => 'search term',
    ]);
});

it('refines all', function () {
    expect(Refine::query($this->builder))
        ->toBeInstanceOf(Refine::class)
        ->for($this->request)->toBeInstanceOf(Refine::class)
        ->with($this->refiners)->toBeInstanceOf(Refine::class)
        ->hasSorts()->toBeTrue()
        ->hasFilters()->toBeTrue()
        ->hasSearch()->toBeTrue()
        ->refine()->toBeInstanceOf(Refine::class);

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

it('refines all with fixture', function () {

    expect(RefineFixture::query($this->builder))
        ->toBeInstanceOf(RefineFixture::class)
        ->for($this->request)->toBeInstanceOf(RefineFixture::class)
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
    expect(Refine::make(Product::class)->with($this->refiners)->getQuery())
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
        config('refine.keys.searches') => 'search+term',
        config('refine.keys.matches') => 'description',
    ]);

    Refine::query($this->builder)
        ->with($this->refiners)
        ->for($request)
        ->matches()
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
    Refine::make('non-existent-class')->with($this->refiners)->getQuery();
})->throws(\InvalidArgumentException::class);

it('has array representation', function () {
    $refine = Refine::make(Product::class)
        ->for($this->request)
        ->with($this->refiners);

    expect($refine->refine()->toArray())
        ->toBeArray()
        ->toHaveKeys(['sorts', 'filters', 'search', 'keys'])
        ->{'search'}->toBe('search term')
        ->{'keys'}->scoped(fn ($keys) => $keys
            ->toBeArray()
            ->toHaveKeys(['sorts', 'searches'])
            ->{'searches'}->toBe(config('refine.keys.searches'))
            ->{'sorts'}->toBe(config('refine.keys.sorts'))
        );

    expect($refine->matches()->refine()->toArray())
        ->toBeArray()
        ->toHaveKeys(['sorts', 'filters', 'search', 'searches','keys'])
        ->{'search'}->toBe('search term')
        ->{'searches'}->toHaveCount(2)
        ->{'keys'}->scoped(fn ($keys) => $keys
            ->toBeArray()
            ->toHaveKeys(['sorts', 'searches', 'matches'])
            ->{'searches'}->toBe(config('refine.keys.searches'))
            ->{'sorts'}->toBe(config('refine.keys.sorts'))
            ->{'matches'}->toBe(config('refine.keys.matches'))
        );
});

it('only refines once', function () {
    $refine = Refine::make(Product::class)->with($this->refiners);

    expect($refine->get())->toBeInstanceOf(Collection::class);
    expect($refine->paginate())->toBeInstanceOf(LengthAwarePaginator::class);
});