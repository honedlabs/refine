<?php

declare(strict_types=1);

use Honed\Refine\Filters\SetFilter;
use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Tests\Stubs\Status;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->param = 'status';
    $this->filter = SetFilter::make($this->param);
});

it('can be multiple', function () {
    expect($this->filter)
        ->isMultiple()->toBeFalse()
        ->multiple()->toBe($this->filter)
        ->isMultiple()->toBeTrue();
});

it('has options', function () {
    expect($this->filter)
        ->hasOptions()->toBeFalse()
        ->enum(Status::class)->toBe($this->filter)
        ->hasOptions()->toBeTrue()
        ->getOptions()->scoped(fn ($options) => $options
        ->toBeArray()
        ->toHaveCount(3)
        ->{0}->scoped(fn ($option) => $option
        ->getValue()->toBe(Status::Available->value)
        ->getLabel()->toBe(Status::Available->name)
        ->isActive()->toBeFalse()
        )->{1}->scoped(fn ($option) => $option
        ->getValue()->toBe(Status::Unavailable->value)
        ->getLabel()->toBe(Status::Unavailable->name)
        ->isActive()->toBeFalse()
        )->{2}->scoped(fn ($option) => $option
        ->getValue()->toBe(Status::ComingSoon->value)
        ->getLabel()->toBe(Status::ComingSoon->name)
        ->isActive()->toBeFalse()
        )
        )->options([1, 2, 3])->toBe($this->filter)
        ->getOptions()->scoped(fn ($options) => $options
        ->toBeArray()
        ->toHaveCount(3)
        ->{0}->scoped(fn ($option) => $option
        ->getValue()->toBe(1)
        ->getLabel()->toBe('1')
        ->isActive()->toBeFalse()
        )->{1}->scoped(fn ($option) => $option
        ->getValue()->toBe(2)
        ->getLabel()->toBe('2')
        ->isActive()->toBeFalse()
        )->{2}->scoped(fn ($option) => $option
        ->getValue()->toBe(3)
        ->getLabel()->toBe('3')
        ->isActive()->toBeFalse()
        )
        )->options(collect(Status::cases())
        ->flatMap(fn ($case) => [$case->value => $case->name])
        )->toBe($this->filter)
        ->getOptions()->scoped(fn ($options) => $options
        ->toBeArray()
        ->toHaveCount(3)
        ->{0}->scoped(fn ($option) => $option
        ->getValue()->toBe(Status::Available->value)
        ->getLabel()->toBe(Status::Available->name)
        ->isActive()->toBeFalse()
        )->{1}->scoped(fn ($option) => $option
        ->getValue()->toBe(Status::Unavailable->value)
        ->getLabel()->toBe(Status::Unavailable->name)
        ->isActive()->toBeFalse()
        )->{2}->scoped(fn ($option) => $option
        ->getValue()->toBe(Status::ComingSoon->value)
        ->getLabel()->toBe(Status::ComingSoon->name)
        ->isActive()->toBeFalse()
        )
        );
});

it('filters singular', function () {
    $request = Request::create('/', 'GET', [$this->param => Status::Available->value]);

    expect($this->filter)
        ->multiple(false)->toBe($this->filter)
        ->isMultiple()->toBeFalse()
        ->options(Status::class)->toBe($this->filter)
        ->apply($this->builder, $request)->toBeTrue()
        ->isActive()->toBeTrue()
        ->getValue()->toBe(Status::Available->value)
        ->getOptions()->sequence(
            fn ($option) => $option->isActive()->toBeTrue(),
            fn ($option) => $option->isActive()->toBeFalse(),
            fn ($option) => $option->isActive()->toBeFalse(),
        );

    expect($this->builder->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'type'}->toBe('Basic')
        ->{'column'}->toBe($this->builder->qualifyColumn('status'))
        ->{'value'}->toBe(Status::Available->value)
        ->{'boolean'}->toBe('and')
        );
});

it('filters multiple', function () {
    $request = Request::create('/', 'GET', [$this->param => \sprintf('%s,%s', Status::Available->value, Status::Unavailable->value)]);

    expect($this->filter)
        ->multiple()->toBe($this->filter)
        ->isMultiple()->toBeTrue()
        ->options(Status::class)->toBe($this->filter)
        ->apply($this->builder, $request)->toBeTrue()
        ->isActive()->toBeTrue()
        ->getValue()->toEqual([Status::Available->value, Status::Unavailable->value])
        ->getOptions()->sequence(
            fn ($option) => $option->isActive()->toBeTrue(),
            fn ($option) => $option->isActive()->toBeTrue(),
            fn ($option) => $option->isActive()->toBeFalse(),
        );

    expect($this->builder->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'type'}->toBe('In')
        ->{'column'}->toBe($this->builder->qualifyColumn('status'))
        ->{'values'}->toEqual([Status::Available->value, Status::Unavailable->value])
        ->{'boolean'}->toBe('and')
        );
});

it('value must be in options for singular', function () {
    $v = 'none';
    $request = Request::create('/', 'GET', [$this->param => $v]);

    expect($this->filter)
        ->options(Status::class)->toBe($this->filter)
        ->apply($this->builder, $request)->toBeFalse()
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull()
        ->getOptions()->sequence(
            fn ($option) => $option->isActive()->toBeFalse(),
            fn ($option) => $option->isActive()->toBeFalse(),
            fn ($option) => $option->isActive()->toBeFalse(),
        );

    expect($this->builder->getQuery()->wheres)
        ->toBeArray()
        ->toBeEmpty();
});

it('value must be in options for multiple', function () {
    $request = Request::create('/', 'GET', [$this->param => \sprintf('%s,%s', Status::Available->value, 'none')]);

    expect($this->filter)
        ->multiple()->toBe($this->filter)
        ->options(Status::class)->toBe($this->filter)
        ->apply($this->builder, $request)->toBeTrue()
        ->isActive()->toBeTrue()
        ->getValue()->toEqual([Status::Available->value])
        ->getOptions()->sequence(
            fn ($option) => $option->isActive()->toBeTrue(),
            fn ($option) => $option->isActive()->toBeFalse(),
            fn ($option) => $option->isActive()->toBeFalse(),
        );

    expect($this->builder->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'type'}->toBe('In')
        ->{'column'}->toBe($this->builder->qualifyColumn('status'))
        ->{'values'}->toEqual([Status::Available->value])
        ->{'boolean'}->toBe('and')
        );
});

it('has array representation', function () {
    expect($this->filter)
        ->multiple()->toBe($this->filter)
        ->options([1, 2, 3])->toBe($this->filter)
        ->toArray()->toBeArray()
        ->toHaveKeys([
            'name',
            'label',
            'type',
            'active',
            'meta',
            'value',
            'multiple',
            'options',
        ]);
});
