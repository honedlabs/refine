<?php

declare(strict_types=1);

use Honed\Refine\Search;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->search = 'search term';
    $this->key = config('refine.searches_key');
});

it('does not apply', function () {
    $name = 'name';

    $search = Search::make($name);

    expect($search)
        ->refine($this->builder, null)->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();

    expect($search)
        ->isActive()->toBeFalse()
        ->getValue()->toBeNull();
});

it('applies', function () {
    $name = 'name';
    $term = 'search term';

    $search = Search::make($name);

    expect($search)
        ->refine($this->builder, $term)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlySearch($this->builder->qualifyColumn($name));

    expect($search)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($term);
});

it('applies boolean', function () {
    $name = 'name';
    $term = 'search term';

    $search = Search::make($name)->boolean('or');

    expect($search)
        ->refine($this->builder, $term)->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toBeOnlySearch($this->builder->qualifyColumn($name), 'or');

    expect($search)
        ->isActive()->toBeTrue()
        ->getValue()->toBe($term)
        ->getBoolean()->toBe('or');
});