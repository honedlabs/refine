<?php

declare(strict_types=1);

use Carbon\Carbon;
use Honed\Refine\Filter;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
});

it('uses alias over name', function () {
    $name = 'name';
    $alias = 'alias';
    $value = 'test';

    $request = Request::create('/', 'GET', [$alias => $value]);

    $filter = Filter::make($name)->alias($alias);

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($name), $value);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value);
});

it('scopes the filter query parameter', function () {
    $name = 'name';
    $alias = 'alias';
    $scope = 'scope';
    $value = 'value';

    $filter = Filter::make($name)
        ->alias($alias)
        ->scope($scope);

    $key = $filter->formatScope($alias);
    $request = Request::create('/', 'GET', [$key => $value]);

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($name), $value);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value);
});

it('requires the parameter name to be present', function () {
    $name = 'name';
    $alias = 'alias';
    $value = 'test';

    $request = Request::create('/', 'GET', [$name => $value]);

    $filter = Filter::make($name)->alias($alias);

    expect($filter->refine($this->builder, $request))
        ->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
});


it('uses `where` by default', function () {
    $name = 'name';
    $value = 'test';

    $request = Request::create('/', 'GET', [$name => $value]);

    $filter = Filter::make($name);

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($name), $value);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value);
});

it('can change the `where` operator', function () {
    $name = 'price';
    $value = 5;
    $operator = '>';

    $request = Request::create('/', 'GET', [$name => $value]);

    $filter = Filter::make($name)
        ->operator($operator);

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($name), $value, $operator);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value);
});

it('can use `like` operators', function () {
    $name = 'name';
    $value = 'test';

    $request = Request::create('/', 'GET', [$name => $value]);

    $filter = Filter::make($name)
        ->operator('like');

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch($this->builder->qualifyColumn($name));

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value);
});

it('can use dates', function () {
    $name = 'created_at';
    $value = Carbon::now();
    $operator = '>=';

    $request = Request::create('/', 'GET', [$name => $value->toDateTimeString()]);

    $filter = Filter::make($name)
        ->operator($operator)
        ->as('date');

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Date')
            ->{'column'}->toBe($this->builder->qualifyColumn($name))
            ->{'operator'}->toBe($operator)
            ->{'value'}->toBe($value->toDateString())
            ->{'boolean'}->toBe('and')
        );

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBeInstanceOf(Carbon::class);
});

it('can use times', function () {
    $name = 'created_at';
    $value = Carbon::now();
    $operator = '>=';

    $request = Request::create('/', 'GET', [$name => $value->toDateTimeString()]);

    $filter = Filter::make($name)
        ->operator($operator)
        ->as('time');

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Time')
            ->{'column'}->toBe($this->builder->qualifyColumn($name))
            ->{'operator'}->toBe($operator)
            ->{'value'}->toBe($value->toDateTimeString())
            ->{'boolean'}->toBe('and')
        );

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value->toDateTimeString());
});

it('supports closures', function () {
    $name = 'name';
    $value = 'test';

    $request = Request::create('/', 'GET', [$name => $value]);

    $filter = Filter::make($name)
        ->using(fn ($builder, $value) => $builder->where($name, 'like', $value.'%'));

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($name, $value.'%', 'like');

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value);
});


it('supports reference only clauses (`has` method)', function () {
    $name = 'details';
    $filter = Filter::make($name)
        ->has('details');

    $request = Request::create('/', 'GET', [$name => 'true']);

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Exists')
        );
        
    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe('true');
});

it('supports reference, explicit operator, value clauses (`where` method)', function () {
    $name = 'quantity';
    $value = 10;
    $operator = '>=';

    $request = Request::create('/', 'GET', [$name => $value]);
    
    $filter = Filter::make($name)
        ->where('quantity', $operator, ':value');

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($name, $value, $operator);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value);
});

it('supports reference, implicit operator, value clauses (`whereRelation` method)', function () {
    $name = 'quantity';
    $value = 10;
    $operator = '>=';

    $request = Request::create('/', 'GET', [$name => $value]);
    
    $filter = Filter::make($name)
        ->whereRelation('details', ':column', $operator, ':value');

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Exists')
        );

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value);
});

it('supports reference, closure clauses (`whereHas` method)', function () {
    $name = 'quantity';
    $value = 10;

    $request = Request::create('/', 'GET', [$name => $value]);
    
    // Rebinds closures
    $filter = Filter::make($name)
        ->whereHas('details', fn ($query, $value) => $query
            ->where('quantity', '>=', $value)
        );

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Exists')
        );

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value);
});

it('supports binding names', function () {
    $name = 'quantity';
    $value = 10;
    $operator = '>=';

    $request = Request::create('/', 'GET', [$name => $value]);

    $filter = Filter::make($name)
        ->where(':table.:column', '>=', ':value');

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere('products.quantity', $value, $operator);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value);
});
