<?php

declare(strict_types=1);

use Honed\Refine\Filter;
use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Tests\Stubs\Status;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->name = 'test';
    $this->filter = Filter::make($this->name);
});

it('accepts options from list', function () {
    expect($this->filter)
        ->hasOptions()->toBeFalse()
        ->options([1, 2, 3, 5])->toBe($this->filter)
        ->hasOptions()->toBeTrue()
        ->getOptions()->toHaveCount(4);
});

it('accepts options from enum', function () {
    expect($this->filter)
        ->hasOptions()->toBeFalse()
        ->options(Status::class)->toBe($this->filter)
        ->hasOptions()->toBeTrue()
        ->getOptions()->toHaveCount(\count(Status::cases()));
});

it('has enum shorthand', function () {
    expect($this->filter)
        ->hasOptions()->toBeFalse()
        ->enum(Status::class)->toBe($this->filter)
        ->hasOptions()->toBeTrue()
        ->getOptions()->toHaveCount(\count(Status::cases()));
});

it('accepts options from associative array', function () {
    expect($this->filter)
        ->hasOptions()->toBeFalse()
        ->options(['active' => 'Active', 'inactive' => 'Inactive'])->toBe($this->filter)
        ->hasOptions()->toBeTrue()
        ->getOptions()->toHaveCount(2)
        ->sequence(
            fn ($option) => $option
                ->getValue()->toBe('active')
                ->getLabel()->toBe('Active'),

            fn ($option) => $option
                ->getValue()->toBe('inactive')
                ->getLabel()->toBe('Inactive'),
        );
});

it('accepts collection', function () {
    expect($this->filter)
        ->hasOptions()->toBeFalse()
        ->options(collect([1, 2, 3, 5]))->toBe($this->filter)
        ->hasOptions()->toBeTrue()
        ->getOptions()->toHaveCount(4);
});

it('can be strict', function () {
    expect($this->filter)
        ->isStrict()->toBe(config('refine.strict'))
        ->strict(true)->toBe($this->filter)
        ->isStrict()->toBe(true)
        ->lax()->toBe($this->filter)
        ->isStrict()->toBe(false);
});

it('can be multiple', function () {
    expect($this->filter)
        ->isMultiple()->toBeFalse()
        ->getAs()->toBeNull()
        ->multiple()->toBe($this->filter)
        ->isMultiple()->toBeTrue()
        ->getType()->toBe('select')
        ->getAs()->toBe('array');
});

it('applies lax', function () {
    $builder = Product::query();

    $filter = Filter::make($this->name)
        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
        ->lax();

    $value = 'indeterminate';

    $request = Request::create('/', 'GET', [$this->name => $value]);

    expect($filter->apply($builder, $request))
        ->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeOnlyWhere($builder->qualifyColumn($this->name), $value);

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value)
        ->getOptions()->each(fn ($option) => $option->isActive()->toBeFalse());
});

it('applies strict', function () {
    $builder = Product::query();

    $filter = Filter::make($this->name)
        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
        ->strict();

    $value = 'indeterminate';

    $request = Request::create('/', 'GET', [$this->name => $value]);

    expect($filter->apply($builder, $request))
        ->toBeFalse();

    expect($builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($filter)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($value)
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

    $filter = Filter::make($this->name)
        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
        ->multiple();

    $value = ['active', 'inactive'];
    $valueString = \implode(',', $value);

    $request = Request::create('/', 'GET', [$this->name => $valueString]);

    expect($filter->apply($builder, $request))
        ->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->toBeOnlyWhereIn($builder->qualifyColumn($this->name), $value);

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
