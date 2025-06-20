<?php

declare(strict_types=1);

use Honed\Refine\Filters\Filter;

beforeEach(function () {
    $this->filter = Filter::make('name');
});

it('sets operator', function () {
    expect($this->filter)
        ->getOperator()->toBe('=')
        ->operator('>')->toBe($this->filter)
        ->getOperator()->toBe('>')
        ->operator('like')->toBe($this->filter)
        ->getOperator()->toBe('LIKE');
});

it('sets greater than', function () {
    expect($this->filter)
        ->greaterThan()->toBe($this->filter)
        ->getOperator()->toBe('>')
        ->gt()->toBe($this->filter)
        ->getOperator()->toBe('>');
});

it('sets greater than or equal to', function () {
    expect($this->filter)
        ->greaterThanOrEqualTo()->toBe($this->filter)
        ->getOperator()->toBe('>=')
        ->gte()->toBe($this->filter)
        ->getOperator()->toBe('>=');
});

it('sets less than', function () {
    expect($this->filter)
        ->lessThan()->toBe($this->filter)
        ->getOperator()->toBe('<')
        ->lt()->toBe($this->filter)
        ->getOperator()->toBe('<');
});

it('sets less than or equal to', function () {
    expect($this->filter)
        ->lessThanOrEqualTo()->toBe($this->filter)
        ->getOperator()->toBe('<=')
        ->lte()->toBe($this->filter)
        ->getOperator()->toBe('<=');
});

it('sets not equal to', function () {
    expect($this->filter)
        ->notEqualTo()->toBe($this->filter)
        ->getOperator()->toBe('!=')
        ->neq()->toBe($this->filter)
        ->getOperator()->toBe('!=');
});

it('sets equals', function () {
    expect($this->filter)
        ->equals()->toBe($this->filter)
        ->getOperator()->toBe('=')
        ->eq()->toBe($this->filter)
        ->getOperator()->toBe('=');
});

it('sets like', function () {
    expect($this->filter)
        ->like()->toBe($this->filter)
        ->getOperator()->toBe('LIKE');
});
