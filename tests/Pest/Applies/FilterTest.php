<?php

declare(strict_types=1);

use Carbon\Carbon;
use Honed\Refine\Filter;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->name = 'name';
    $this->alias = 'alias';
    $this->value = 'value';
    $this->filter = Filter::make($this->name);
});

// it('does not apply', function () {

// })

it('applies', function () {
    // It should not apply if the name does not match.
    $request = generate('random', $this->value);

    expect($this->filter->refine($this->builder, $request))
        ->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
        
    // It should apply if the name matches the query parameter.
    $request = generate($this->name, $this->value);

    expect($this->filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($this->name), $this->value);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with alias', function () {
    $filter = $this->filter->alias($this->alias);

    // It should not apply if the alias does not match.
    $request = Request::create('/', 'GET', [$this->name => $this->value]);

    expect($this->filter->refine($this->builder, $request))
        ->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();
    
    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();

    // It should apply if the alias matches.
    $request = Request::create('/', 'GET', [$this->alias => $this->value]);

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($this->name), $this->value);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with scope', function () {
    $scope = 'scope';
    $filter = $this->filter->alias($this->alias)->scope($scope);

    // It should not apply if the scope does not match.
    $request = generate($this->alias, $this->value);

    expect($filter->refine($this->builder, $request))
        ->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();

    // It should apply if the scope and alias match.
    $key = $this->filter->formatScope($this->alias);
    $request = generate($key, $this->value);

    expect($this->filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($this->name), $this->value);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with different operator', function () {
    $operator = '>';

    $request = generate($this->name, $this->value);

    $filter = $this->filter->operator($operator);

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($this->name), $this->value, $operator);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with `like` operators', function () {
    $request = generate($this->name, $this->value);

    $filter = $this->filter->operator('like');

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch($this->builder->qualifyColumn($this->name));

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with date', function () {
    $value = Carbon::now();

    $request = generate($this->name, $value->toIso8601String());

    $filter = $this->filter->date();

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Date')
            ->{'column'}->toBe($this->builder->qualifyColumn($this->name))
            ->{'operator'}->toBe('=')
            ->{'value'}->toBe($value->toDateString())
            ->{'boolean'}->toBe('and')
        );

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBeInstanceOf(Carbon::class);
});

it('applies with datetime', function () {
    $value = Carbon::now();

    $request = generate($this->name, $value->toIso8601String());

    $filter = $this->filter->datetime();

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Basic')
            ->{'column'}->toBe($this->builder->qualifyColumn($this->name))
            ->{'operator'}->toBe('=')
            ->{'value'}->toBeInstanceOf(Carbon::class)
            ->{'boolean'}->toBe('and')
        );

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBeInstanceOf(Carbon::class);
});

it('applies with time', function () {
    $value = Carbon::now();

    $request = generate($this->name, $value->toIso8601String());

    $filter = $this->filter->time();

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Time')
            ->{'column'}->toBe($this->builder->qualifyColumn($this->name))
            ->{'operator'}->toBe('=')
            ->{'value'}->toBe($value->toTimeString())
            ->{'boolean'}->toBe('and')
        );

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBeInstanceOf(Carbon::class);
});

it('applies with query', function () {
    $request = generate($this->name, $this->value);

    $filter = $this->filter->query(fn ($builder, $value) => $builder->where($this->name, 'like', $value.'%'));

    expect($filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->name, $this->value.'%', 'like');

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies lax', function () {
    $builder = Product::query();

    $filter = Filter::make('status')
        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
        ->lax();

    $value = 'indeterminate';

    $request = Request::create('/', 'GET', ['status' => $value]);

    expect($filter->refine($builder, $request))
        ->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeOnlyWhere($builder->qualifyColumn('status'), $value);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value)
        ->getOptions()->each(fn ($option) => $option->isActive()->toBeFalse());
});

it('applies strict', function () {
    $builder = Product::query();

    $filter = Filter::make('status')
        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
        ->strict();

    $value = 'indeterminate';

    $request = Request::create('/', 'GET', ['status' => $value]);

    expect($filter->refine($builder, $request))
        ->toBeFalse();

    expect($builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull() // Transform means invalid values are discarded
        ->getOptions()->each(fn ($option) => $option->isActive()->toBeFalse())
        ->optionsToArray()->toEqual([
            [
                'value' => 'active',
                'label' => 'Active',
                'active' => false,
            ],
            [
                'value' => 'inactive',
                'label' => 'Inactive',
                'active' => false,
            ],
        ]);
});

it('applies multiple', function () {
    $builder = Product::query();

    $filter = Filter::make('status')
        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
        ->multiple();

    $value = ['active', 'inactive'];
    $valueString = \implode(',', $value);

    $request = Request::create('/', 'GET', ['status' => $valueString]);

    expect($filter->refine($builder, $request))
        ->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeOnlyWhereIn($builder->qualifyColumn('status'), $value);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value)
        ->getOptions()->each(fn ($option) => $option->isActive()->toBeTrue())
        ->optionsToArray()->toEqual([
            [
                'value' => 'active',
                'label' => 'Active',
                'active' => true,
            ],
            [
                'value' => 'inactive',
                'label' => 'Inactive',
                'active' => true,
            ],
        ]);
});
