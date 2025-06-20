<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;
use Workbench\App\Enums\Status;

beforeEach(function () {
    $this->filter = Filter::make('name');
});

it('creates', function () {
    expect($this->filter)
        ->getType()->toBe('filter')
        ->interpretsAs()->toBeNull();
});

it('can be boolean', function () {
    expect($this->filter)
        ->boolean()->toBe($this->filter)
        ->getType()->toBe(Filter::BOOLEAN)
        ->interpretsAs()->toBe('boolean');
});

it('can be date', function () {
    expect($this->filter)
        ->date()->toBe($this->filter)
        ->getType()->toBe(Filter::DATE)
        ->interpretsAs()->toBe('date');
});

it('can be date time', function () {
    expect($this->filter)
        ->datetime()->toBe($this->filter)
        ->getType()->toBe(Filter::DATETIME)
        ->interpretsAs()->toBe('datetime');
});

it('can be float', function () {
    expect($this->filter)
        ->float()->toBe($this->filter)
        ->getType()->toBe(Filter::NUMBER)
        ->interpretsAs()->toBe('float');
});

it('can be integer', function () {
    expect($this->filter)
        ->int()->toBe($this->filter)
        ->getType()->toBe(Filter::NUMBER)
        ->interpretsAs()->toBe('int');
});

it('can be array multiple', function () {
    expect($this->filter)
        ->multiple()->toBe($this->filter)
        ->getType()->toBe(Filter::SELECT)
        ->interpretsAs()->toBe('array')
        ->isMultiple()->toBeTrue();
});

it('can be text', function () {
    expect($this->filter)
        ->text()->toBe($this->filter)
        ->getType()->toBe(Filter::TEXT)
        ->interpretsAs()->toBe('string');
});

it('can be time', function () {
    expect($this->filter)
        ->time()->toBe($this->filter)
        ->getType()->toBe(Filter::TIME)
        ->interpretsAs()->toBe('time');
});

it('can be presence', function () {
    expect($this->filter)
        ->presence()->toBe($this->filter)
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
        ->interpretsAs()->toBe('array');
});
