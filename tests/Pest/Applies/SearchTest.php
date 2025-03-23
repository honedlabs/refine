<?php

declare(strict_types=1);

use Honed\Refine\Search;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->search = 'search term';

    $this->test = Search::make('name');
});

it('does not apply', function () {
    expect($this->test)
        ->refine($this->builder, [true, null])->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->test)
        // Activity is independent of the search term
        ->isActive()->toBeTrue(); 
});

it('applies', function () {
    expect($this->test)
        ->refine($this->builder, [true, $this->search])->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch($this->builder->qualifyColumn('name'));

    expect($this->test)
        ->isActive()->toBeTrue();
});

it('applies boolean', function () {
    $this->test->boolean('or');

    expect($this->test)
        ->refine($this->builder, [true, $this->search])->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toBeOnlySearch($this->builder->qualifyColumn('name'), 'or');

    expect($this->test)
        ->isActive()->toBeTrue()
        ->getBoolean()->toBe('or');
});

it('applies full text search', function () {
    $this->test->fullText();

    expect($this->test)
        ->refine($this->builder, [true, $this->search])->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->{0}->{'type'}->toBe('Fulltext');

    expect($this->test)
        ->isActive()->toBeTrue();
});

it('does not apply if inactive', function () {
    expect($this->test)
        ->refine($this->builder, [false, $this->search])->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->test)
        ->isActive()->toBeFalse();
});

