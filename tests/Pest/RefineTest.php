<?php

declare(strict_types=1);

use Honed\Refine\Refine;
use Honed\Refine\Sorts\Sort;
use Honed\Refine\Filters\Filter;
use Illuminate\Support\Collection;
use Honed\Refine\Searches\Search;
use Honed\Refine\Filters\SetFilter;
use Honed\Refine\Filters\DateFilter;
use Honed\Refine\Tests\Stubs\Status;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;
use Honed\Refine\Filters\BooleanFilter;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->builder = Product::query();

    $this->refiners = [
        Filter::make('name')->like(),

        SetFilter::make('price', 'Maximum price')->options([10, 20, 50, 100])->lt(),
        SetFilter::make('status')->enum(Status::class)->multiple(),
        SetFilter::make('status', 'Single')->alias('only')->enum(Status::class),

        BooleanFilter::make('best_seller', 'Favourite')->alias('favourite'),
        
        DateFilter::make('created_at', 'Oldest')->alias('oldest')->gt(),
        DateFilter::make('created_at', 'Newest')->alias('newest')->lt(),

        Sort::make('name', 'A-Z')->alias('name-desc')->desc()->default(),
        Sort::make('name', 'Z-A')->alias('name-asc')->asc(),
        Sort::make('price'),
        Sort::make('best_seller', 'Favourite')->alias('favourite'),

        Search::make('name'),
        Search::make('description'),
    ];
});

it('refines all', function () {
    $request = Request::create('/', 'GET', [
        'name' => 'test',

        'price' => 100, 
        'status' => \sprintf('%s,%s', Status::Available->value, Status::Unavailable->value),
        'only' => Status::ComingSoon->value,

        'favourite' => '1',

        'oldest' => '2000-01-01',
        'newest' => '2001-01-01',

        'sort' => '-price',

        Refine::SearchKey => 'search',
    ]);

    expect(Refine::query($this->builder))
        ->toBeInstanceOf(Refine::class)
        ->for($request)->toBeInstanceOf(Refine::class)
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
                    ->{'type'}->toBe('Basic')
                    ->{'column'}->toBe($this->builder->qualifyColumn('name'))
                    ->{'operator'}->toBe('like')
                    ->{'value'}->toBe('%search%')
                    ->{'boolean'}->toBe('and'),
                fn ($search) => $search
                    ->{'type'}->toBe('Basic')
                    ->{'column'}->toBe($this->builder->qualifyColumn('description'))
                    ->{'operator'}->toBe('like')
                    ->{'value'}->toBe('%search%')
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
    expect(Refine::model(Product::class)->with($this->refiners)->getQuery())
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

it('can filter and then retrieve refiners', function () {
    $request = Request::create('/', 'GET', [
        'price' => 110,
        'favourite' => '1',
    ]);

    $refine = Refine::make($this->builder)->with($this->refiners)->for($request);

    expect($refine->paginate())
        ->toBeInstanceOf(LengthAwarePaginator::class);

    expect($refine->refinements())
        ->toBeArray()
        ->toHaveKeys(['sorts', 'filters', 'searches']);

    expect($this->builder->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($filter) => $filter
            ->{'type'}->toBe('Basic')
            ->{'column'}->toBe($this->builder->qualifyColumn('best_seller'))
            ->{'operator'}->toBe('=')
            ->{'value'}->toBe(true)
            ->{'boolean'}->toBe('and')
        );
});

it('can change the search columns', function () {
    $request = Request::create('/', 'GET', [
        Refine::SearchKey => 'search',
        Refine::ColumnsKey => 'description',
    ]);

    Refine::query($this->builder)->with($this->refiners)->for($request)->refine();

    expect($this->builder->getQuery())
        ->wheres->scoped(fn ($wheres) => $wheres
            ->toBeArray()
            ->toHaveCount(1)
            ->sequence(
                fn ($search) => $search
                    ->{'type'}->toBe('Basic')
                    ->{'column'}->toBe($this->builder->qualifyColumn('description'))
                    ->{'operator'}->toBe('like')
                    ->{'value'}->toBe('%search%')
                    ->{'boolean'}->toBe('and'),
            )
        );

});

it('throws exception when no builder is set', function () {
    Refine::make('non-existent-class')->with($this->refiners)->getQuery();
})->throws(\InvalidArgumentException::class);

it('has array representation', function () {
    expect(Refine::make(Product::class)->with($this->refiners)->toArray())
        ->toBeArray()
        ->toHaveKeys(['sorts', 'filters', 'searches']);

    expect(Refine::make(Product::class)->with($this->refiners)->refinements())
        ->toBeArray()
        ->toHaveKeys(['sorts', 'filters', 'searches']);
});

it('only refines once', function () {
    $refine = Refine::make(Product::class)->with($this->refiners);

    expect($refine->get())->toBeInstanceOf(Collection::class);
    expect($refine->paginate())->toBeInstanceOf(LengthAwarePaginator::class);
});

it('has magic methods', function () {
    expect(Refine::make(Product::class))
        ->addSorts([Sort::make('name', 'A-Z')])->toBeInstanceOf(Refine::class)
        ->getSorts()->toHaveCount(1);

    expect(Refine::make(Product::class))
        ->addFilters([Filter::make('name', 'Name')->like()])->toBeInstanceOf(Refine::class)
        ->getFilters()->toHaveCount(1);
});

it('can change the sort key', function () {
    expect(Refine::make(Product::class))
        ->sortKey('name')->toBeInstanceOf(Refine::class)
        ->getSortKey()->toBe('name');
});

it('can change the search key', function () {
    expect(Refine::make(Product::class))
        ->searchKey('name')->toBeInstanceOf(Refine::class)
        ->getSearchKey()->toBe('name');
});

it('can change columns key', function () {
    expect(Refine::make(Product::class))
        ->columnsKey('name')->toBeInstanceOf(Refine::class)
        ->getColumnsKey()->toBe('name');
});