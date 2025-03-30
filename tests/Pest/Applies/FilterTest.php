<?php

declare(strict_types=1);

use Carbon\Carbon;
use Honed\Refine\Filter;
use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Tests\Stubs\Status;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->name = 'name';
    $this->alias = 'alias';
    $this->value = 'value';
    $this->filter = Filter::make($this->name);
});

it('does not apply', function () {
    $request = Request::create('/', 'GET', ['none' => $this->value]);
    
    expect($this->filter->refine($this->builder, $request))
        ->toBeFalse();
    
    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();
    
    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
});

it('applies', function () {
    $request = Request::create('/', 'GET', [$this->name => $this->value]);

    expect($this->filter->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($this->name), $this->value);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('does not apply with alias', function () {
    $request = Request::create('/', 'GET', [$this->name => $this->value]);

    expect($this->filter->alias($this->alias))
        ->refine($this->builder, $request)->toBeFalse();
        
    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();
    
    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
});

it('applies with alias', function () {
    $request = Request::create('/', 'GET', [$this->alias => $this->value]);

    expect($this->filter->alias($this->alias))
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($this->name), $this->value);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('does not apply with scope', function () {
    $scope = 'scope';

    $request = Request::create('/', 'GET', [$this->name => $this->value]);

    expect($this->filter->scope($scope))
        ->refine($this->builder, $request)->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();
    
    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
});

it('applies with scope', function () {
    $this->filter->scope('scope');

    $request = Request::create('/', 'GET', [
        $this->filter->formatScope($this->name) => $this->value
    ]);

    expect($this->filter)
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($this->name), $this->value);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with different operator', function () {
    $operator = '>';

    $request = Request::create('/', 'GET', [$this->name => $this->value]);

    expect($this->filter->operator($operator))
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($this->name), $this->value, $operator);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with `like` operators', function () {
    $request = Request::create('/', 'GET', [$this->name => $this->value]);

    expect($this->filter->operator('like'))
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch($this->builder->qualifyColumn($this->name));

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with date', function () {
    $value = Carbon::now();

    $request = Request::create('/', 'GET', [
        $this->name => $value->toIso8601String()
    ]);

    expect($this->filter->date())
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Date')
            ->{'column'}->toBe($this->builder->qualifyColumn($this->name))
            ->{'operator'}->toBe('=')
            ->{'value'}->toBe($value->toDateString())
            ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBeInstanceOf(Carbon::class);
});

it('applies with datetime', function () {
    $value = Carbon::now();

    $request = Request::create('/', 'GET', [
        $this->name => $value->toIso8601String()
    ]);

    expect($this->filter->datetime())
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Basic')
            ->{'column'}->toBe($this->builder->qualifyColumn($this->name))
            ->{'operator'}->toBe('=')
            ->{'value'}->toBeInstanceOf(Carbon::class)
            ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBeInstanceOf(Carbon::class);
});

it('applies with time', function () {
    $value = Carbon::now();

    $request = Request::create('/', 'GET', [
        $this->name => $value->toIso8601String()
    ]);

    expect($this->filter->time())
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
            ->{'type'}->toBe('Time')
            ->{'column'}->toBe($this->builder->qualifyColumn($this->name))
            ->{'operator'}->toBe('=')
            ->{'value'}->toBe($value->toTimeString())
            ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBeInstanceOf(Carbon::class);
});

it('applies with query', function () {
    $request = Request::create('/', 'GET', [$this->name => $this->value]);

    $fn = fn ($builder, $value) => $builder->where($this->name, 'like', $value.'%');

    expect($this->filter->query($fn))
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->name, $this->value.'%', 'like');

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies lax', function () {
    $value = 'invalid';

    $request = Request::create('/', 'GET', [$this->name => $value]);

    expect($this->filter->lax()->options(Status::class))
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($this->name), $value);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value)
        ->getOptions()->each(fn ($option) => $option->isActive()->toBeFalse());
});

it('applies strict', function () {
    $value = 'indeterminate';

    $request = Request::create('/', 'GET', [$this->name => $value]);

    expect($this->filter->strict()->options(Status::class))
        ->refine($this->builder, $request)->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull() // Transform means invalid values are discarded
        ->getOptions()->each(fn ($option) => $option->isActive()->toBeFalse());
});

it('applies multiple', function () {
    $value = [Status::Available->value, Status::Unavailable->value];
    $values = \implode(',', $value);

    $request = Request::create('/', 'GET', [$this->name => $values]);

    expect($this->filter->multiple()->options(Status::class))
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhereIn($this->builder->qualifyColumn($this->name), $value);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toEqual($value)
        ->getOptions()->scoped(fn ($options) => $options
            ->toBeArray()
            ->toHaveCount(3)
            ->{0}->isActive()->toBeTrue()
            ->{1}->isActive()->toBeTrue()
            ->{2}->isActive()->toBeFalse()
        );
});

it('applies with unqualified column', function () {
    $request = Request::create('/', 'GET', [$this->name => 'value']);

    expect($this->filter->unqualify()->refine($this->builder, $request))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->name, 'value');

    expect($this->filter)
        ->isActive()->toBeTrue();
});
