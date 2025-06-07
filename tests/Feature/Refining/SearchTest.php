<?php

declare(strict_types=1);

use Honed\Refine\Search;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->test = Search::make('name');
});

afterEach(function () {
    Search::flushState();
});

it('does not apply', function () {
    expect($this->test)
        ->refine($this->builder, [true, null])->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->test)
        ->isActive()->toBeTrue();
});

it('applies', function () {
    expect($this->test)
        ->refine($this->builder, [true, 'term'])->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch('name');

    expect($this->test)
        ->isActive()->toBeTrue();
});

it('applies boolean', function () {
    $this->test->boolean('or');

    expect($this->test)
        ->refine($this->builder, [true, 'term'])->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch('name', 'or');

    expect($this->test)
        ->isActive()->toBeTrue()
        ->getBoolean()->toBe('or');
});

it('applies full text search', function () {
    $this->test->fullText();

    expect($this->test)
        ->refine($this->builder, [true, 'term'])->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->{0}->{'type'}->toBe('Fulltext');

    expect($this->test)
        ->isActive()->toBeTrue();
});

it('does not apply if inactive', function () {
    expect($this->test)
        ->refine($this->builder, [false, 'term'])->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->test)
        ->isActive()->toBeFalse();
});

it('applies with qualified column', function () {
    expect($this->test->qualify())
        ->refine($this->builder, [true, 'term'])->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch($this->builder->qualifyColumn('name'));

    expect($this->test)
        ->qualifies()->toBeTrue()
        ->isActive()->toBeTrue();
});
