<?php

declare(strict_types=1);

use Honed\Refine\Searches\Search;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->test = Search::make('name');
});

it('requires a search term', function () {
    expect($this->test)
        ->handle($this->builder, null, null, false)->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($this->test)
        ->isActive()->toBeTrue();
});

it('applies without columns', function () {
    expect($this->test)
        ->handle($this->builder, 'term', null, false)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch('name');

    expect($this->test)
        ->isActive()->toBeTrue();
});

it('applies boolean', function () {
    expect($this->test)
        ->handle($this->builder, 'term', null, true)->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch('name', 'or');

    expect($this->test)
        ->isActive()->toBeTrue();
});

it('applies full text search', function () {
    $this->test->fullText();

    expect($this->test)
        ->handle($this->builder, 'term', null, false)->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->{0}->{'type'}->toBe('Fulltext');

    expect($this->test)
        ->isActive()->toBeTrue();
});

it('applies with qualified column', function () {
    expect($this->test->qualify())
        ->handle($this->builder, 'term', null, false)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch($this->builder->qualifyColumn('name'));

    expect($this->test)
        ->isQualifying()->toBeTrue()
        ->isActive()->toBeTrue();
});
