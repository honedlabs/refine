<?php

declare(strict_types=1);

use Honed\Refine\Filter;
use Workbench\App\Enums\Status;

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

it('can be raw', function () {
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

it('can be presence', function () {
    expect($this->filter)
        ->presence()->toBe($this->filter)
        ->getType()->toBe('boolean')
        ->interpretsAs()->toBe('boolean')
        ->isPresence()->toBeTrue();
});

it('has default', function () {
    expect($this->filter)
        ->getDefault()->toBeNull()
        ->default('value')->toBe($this->filter)
        ->getDefault()->toBe('value');
});

it('has enum shorthand', function () {
    expect($this->filter)
        ->hasOptions()->toBeFalse()
        ->enum(Status::class)->toBe($this->filter)
        ->hasOptions()->toBeTrue()
        ->getOptions()->toHaveCount(\count(Status::cases()));
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
