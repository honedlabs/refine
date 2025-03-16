<?php

declare(strict_types=1);

use Carbon\Carbon;
use Honed\Refine\Filter;
use Honed\Refine\Tests\Stubs\Status;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->filter = Filter::make('name');
});

it('has operator', function () {
    expect($this->filter)
        ->getOperator()->toBe('=')
        ->operator('!=')->toBe($this->filter)
        ->getOperator()->toBe('!=');
});

it('can set filter types', function () {
    expect($this->filter)
        ->getType()->toBe('filter')
        ->getAs()->toBeNull();

    expect($this->filter->boolean())
        ->getType()->toBe('boolean')
        ->getAs()->toBe('boolean');

    expect($this->filter->date())
        ->getType()->toBe('date')
        ->getAs()->toBe('date');

    expect($this->filter->dateTime())
        ->getType()->toBe('datetime')
        ->getAs()->toBe('datetime');

    expect($this->filter->float())
        ->getType()->toBe('float')
        ->getAs()->toBe('float');

    expect($this->filter->integer())
        ->getType()->toBe('integer')
        ->getAs()->toBe('integer');

    expect($this->filter->multiple())
        ->getType()->toBe('multiple')
        ->getAs()->toBe('array')
        ->isMultiple()->toBeTrue();

    expect($this->filter->string())
        ->getType()->toBe('string')
        ->getAs()->toBe('string');

    expect($this->filter->time())
        ->getType()->toBe('time')
        ->getAs()->toBe('time');
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
        ->getType()->toBe('multiple')
        ->getAs()->toBe('array');
});
