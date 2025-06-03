<?php

declare(strict_types=1);

use Honed\Refine\Sort;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->name = 'name';
    $this->sort = Sort::make($this->name);
});

it('does not apply', function () {
    expect($this->sort->refine($this->builder, ['other', 'asc']))
        ->toBeFalse();

    expect($this->builder->getQuery()->orders)
        ->toBeEmpty();

    expect($this->sort)
        ->isActive()->toBeFalse()
        ->getDirection()->toBeNull()
        ->getNextDirection()->toBe($this->name);
});

it('applies alias', function () {
    $alias = 'alphabetical';

    $sort = $this->sort->alias($alias);

    expect($sort->refine($this->builder, [$this->name, 'asc']))
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
        ->toBeOnlyOrder($this->builder->qualifyColumn($this->name), 'asc');

    expect($sort)
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('asc')
        ->getNextDirection()->toBe('-'.$alias);
});

it('applies fixed direction', function () {
    $sort = $this->sort->desc();

    $descending = $this->name.'_desc';

    expect($sort->refine($this->builder, [$descending, 'desc']))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn($this->name), 'desc');

    expect($sort)
        ->isFixed()->toBeTrue()
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('desc')
        ->getNextDirection()->toBe($descending);
});

it('applies inverted direction', function () {
    $sort = $this->sort->invert();

    expect($sort->refine($this->builder, [$this->name, 'desc']))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn($this->name), 'desc');

    expect($sort)
        ->isInverted()->toBeTrue()
        ->isActive()->toBeTrue()
        ->getDirection()->toBe('desc')
        ->getNextDirection()->toBe($this->name);
});

it('applies query', function () {
    $column = 'created_at';

    $sort = $this->sort
        ->query(fn ($builder, $direction) => $builder->orderBy($column, $direction));

    expect($sort->refine($this->builder, [$this->name, 'desc']))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($column, 'desc');

    expect($sort)
        ->isActive()->toBeTrue();
});

it('applies with unqualified column', function () {
    expect($this->sort->qualify(false))
        ->refine($this->builder, [$this->name, 'asc'])->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->name, 'asc');

    expect($this->sort)
        ->isQualifying()->toBeFalse()
        ->isActive()->toBeTrue();
});
