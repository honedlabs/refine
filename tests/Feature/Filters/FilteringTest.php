<?php

declare(strict_types=1);

use Carbon\Carbon;
use Honed\Refine\Filters\Filter;
use Workbench\App\Enums\Status;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->name = 'name';
    $this->alias = 'alias';
    $this->value = 'value';
    $this->filter = Filter::make($this->name);
});

it('does not apply', function () {
    expect($this->filter->handle($this->builder, null))
        ->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
});

it('applies', function () {
    expect($this->filter->handle($this->builder, 'value'))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->name, $this->value);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with default', function () {
    expect($this->filter->default('value')->handle($this->builder, null))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->name, 'value');

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe('value');
});

it('applies with different operator', function () {
    $operator = '>';

    expect($this->filter->operator($operator))
        ->handle($this->builder, $this->value)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->name, $this->value, $operator);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with `like` operators', function () {
    expect($this->filter->operator('like'))
        ->handle($this->builder, $this->value)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch($this->name);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies with date', function () {
    $value = Carbon::now();

    expect($this->filter->date())
        ->handle($this->builder, $value)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}
        ->scoped(fn ($where) => $where
            ->{'type'}->toBe('Date')
            ->{'column'}->toBe($this->name)
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

    expect($this->filter->datetime())
        ->handle($this->builder, $value)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}
        ->scoped(fn ($where) => $where
            ->{'type'}->toBe('Basic')
            ->{'column'}->toBe($this->name)
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

    expect($this->filter->time())
        ->handle($this->builder, $value)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toHaveCount(1)
        ->{0}
        ->scoped(fn ($where) => $where
            ->{'type'}->toBe('Time')
            ->{'column'}->toBe($this->name)
            ->{'operator'}->toBe('=')
            ->{'value'}->toBe($value->toTimeString())
            ->{'boolean'}->toBe('and')
        );

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBeInstanceOf(Carbon::class);
});

it('applies with query', function () {
    $fn = fn ($builder, $value) => $builder->where($this->name, 'like', $value.'%');

    expect($this->filter->query($fn))
        ->handle($this->builder, $this->value)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->name, $this->value.'%', 'like');

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($this->value);
});

it('applies lax', function () {
    $value = 'invalid';

    expect($this->filter->lax()->options(Status::class))
        ->handle($this->builder, $value)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->name, $value);

    expect($this->filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value)
        ->getOptions()->each(fn ($option) => $option->isActive()->toBeFalse());
});

it('applies strict', function () {
    $value = 'indeterminate';

    expect($this->filter->strict()->options(Status::class))
        ->handle($this->builder, $value)->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->filter)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull() // Transform means invalid values are discarded
        ->getOptions()->each(fn ($option) => $option->isActive()->toBeFalse());
});

it('applies multiple', function () {
    $value = [Status::Available->value, Status::Unavailable->value];

    expect($this->filter->multiple()->options(Status::class))
        ->handle($this->builder, $value)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhereIn($this->name, $value);

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

it('applies with qualified column', function () {
    expect($this->filter->qualify()->handle($this->builder, 'value'))
        ->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere($this->builder->qualifyColumn($this->name), 'value');

    expect($this->filter)
        ->isActive()->toBeTrue();
});
