<?php

declare(strict_types=1);

use Honed\Refine\Filter;
use Honed\Refine\Tests\Stubs\Status;

beforeEach(function () {
    $this->filter = Filter::make('name');
});

it('has operator', function () {
    expect($this->filter)
        ->getOperator()->toBe('=')
        ->operator('!=')->toBe($this->filter)
        ->getOperator()->toBe('!=')
        ->gt()->toBe($this->filter)
        ->getOperator()->toBe('>')
        ->gte()->toBe($this->filter)
        ->getOperator()->toBe('>=')
        ->lt()->toBe($this->filter)
        ->getOperator()->toBe('<')
        ->lte()->toBe($this->filter)
        ->getOperator()->toBe('<=')
        ->neq()->toBe($this->filter)
        ->getOperator()->toBe('!=')
        ->eq()->toBe($this->filter)
        ->getOperator()->toBe('=');
});

it('can be default', function () {
    expect($this->filter)
        ->getType()->toBe('filter')
        ->interpretsAs()->toBeNull();
});

it('can be boolean', function () {
    expect($this->filter)
        ->boolean()->toBe($this->filter)
        ->getType()->toBe('boolean')
        ->interpretsAs()->toBe('boolean');
});

it('can be date', function () {
    expect($this->filter)
        ->date()->toBe($this->filter)
        ->getType()->toBe('date')
        ->interpretsAs()->toBe('date');
});

it('can be date time', function () {
    expect($this->filter)
        ->datetime()->toBe($this->filter)
        ->getType()->toBe('datetime')
        ->interpretsAs()->toBe('datetime');
});

it('can be float', function () {
    expect($this->filter)
        ->float()->toBe($this->filter)
        ->getType()->toBe('number')
        ->interpretsAs()->toBe('float');
});

it('can be integer', function () {
    expect($this->filter)
        ->int()->toBe($this->filter)
        ->getType()->toBe('number')
        ->interpretsAs()->toBe('int');
});

it('can be array multiple', function () {
    expect($this->filter)
        ->multiple()->toBe($this->filter)
        ->getType()->toBe('multiple')
        ->interpretsAs()->toBe('array')
        ->isMultiple()->toBeTrue();
});

it('can be text', function () {
    expect($this->filter)
        ->text()->toBe($this->filter)
        ->getType()->toBe('text')
        ->interpretsAs()->toBe('string');
});

it('can be time', function () {
    expect($this->filter)
        ->time()->toBe($this->filter)
        ->getType()->toBe('time')
        ->interpretsAs()->toBe('time');
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
        ->interpretsAs()->toBeNull()
        ->multiple()->toBe($this->filter)
        ->isMultiple()->toBeTrue()
        ->getType()->toBe('multiple')
        ->interpretsAs()->toBe('array');
});
