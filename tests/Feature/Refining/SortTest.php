<?php

declare(strict_types=1);

use Honed\Refine\Sort;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->sort = Sort::make('name');
});

it('does not apply', function () {
    expect($this->sort->refine($this->builder, ['other', 'asc']))
        ->toBeFalse();

    expect($this->builder->getQuery()->orders)
        ->toBeEmpty();

    expect($this->sort)
        ->isActive()->toBeFalse()
        ->getDirection()->toBeNull()
        ->getNextDirection()->toBe('name');
});

it('applies alias', function () {
    $alias = 'alphabetical';

    $sort = $this->sort->alias($alias);

    expect($sort->refine($this->builder, ['name', 'asc']))
        ->toBeFalse();

    expect($this->builder->getQuery()->orders)
        ->toBeEmpty();

    expect($sort)
        ->isActive()->toBeFalse()
        ->getDirection()->toBeNull()
        ->getNextDirection()->toBe($alias);

    // Should apply

    expect($sort->refine($this->builder, [$alias, 'asc']))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder('name', 'asc');

    expect($sort)
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('asc')
        ->getNextDirection()->toBe('-'.$alias);
});

it('applies fixed direction', function () {
    $sort = $this->sort->desc();

    $descending = 'name'.'_desc';

    expect($sort->refine($this->builder, [$descending, 'desc']))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder('name', 'desc');

    expect($sort)
        ->isFixed()->toBeTrue()
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('desc')
        ->getNextDirection()->toBe($descending);
});

it('applies inverted direction', function () {
    $sort = $this->sort->invert();

    expect($sort->refine($this->builder, ['name', 'desc']))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder('name', 'desc');

    expect($sort)
        ->isInverted()->toBeTrue()
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('desc')
        ->getNextDirection()->toBe('name');
});

it('applies query', function () {
    $column = 'created_at';

    $sort = $this->sort
        ->query(fn ($builder, $direction) => $builder->orderBy($column, $direction));

    expect($sort->refine($this->builder, ['name', 'desc']))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($column, 'desc');

    expect($sort)
        ->isActive()->toBeTrue();
});

it('applies with qualified column', function () {
    expect($this->sort->qualify())
        ->refine($this->builder, ['name', 'asc'])->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn('name'), 'asc');

    expect($this->sort)
        ->qualifies()->toBeTrue()
        ->isActive()->toBeTrue();
});
