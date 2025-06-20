<?php

declare(strict_types=1);

use Honed\Refine\Sorts\Sort;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->name = 'name';
    $this->sort = Sort::make($this->name);
});

it('does not apply', function () {
    expect($this->sort->handle($this->builder, 'other', Sort::ASCENDING))
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

    expect($sort->handle($this->builder, $this->name, Sort::ASCENDING))
        ->toBeFalse();

    expect($this->builder->getQuery()->orders)
        ->toBeEmpty();

    expect($sort)
        ->isActive()->toBeFalse()
        ->getDirection()->toBeNull()
        ->getNextDirection()->toBe($alias);

    expect($sort->handle($this->builder, $alias, Sort::ASCENDING))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->name, Sort::ASCENDING);

    expect($sort)
        ->isActive()->toBeTrue()
        ->getDirection()->toBe(Sort::ASCENDING)
        ->getNextDirection()->toBe('-'.$alias);
});

it('applies enforced direction', function () {
    $sort = $this->sort->desc();

    $descending = $this->name.'_'.Sort::DESCENDING;

    expect($sort->handle($this->builder, $descending, Sort::DESCENDING))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->name, Sort::DESCENDING);

    expect($sort)
        ->enforcesDirection()->toBeTrue()
        ->isActive()->toBeTrue()
        ->getDirection()->toBe(Sort::DESCENDING)
        ->getNextDirection()->toBe($descending);
});

it('applies inverted direction', function () {
    $sort = $this->sort->invert();

    expect($sort->handle($this->builder, $this->name, Sort::DESCENDING))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->name, Sort::DESCENDING);

    expect($sort)
        ->isInverted()->toBeTrue()
        ->isActive()->toBeTrue()
        ->getDirection()->toBe(Sort::DESCENDING)
        ->getNextDirection()->toBe($this->name);
});

it('applies query', function () {
    $column = 'created_at';

    $sort = $this->sort
        ->query(fn ($builder, $direction) => $builder->orderBy($column, $direction));

    expect($sort->handle($this->builder, $this->name, Sort::DESCENDING))
        ->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($column, Sort::DESCENDING);

    expect($sort)
        ->isActive()->toBeTrue();
});

it('applies with qualified column', function () {
    expect($this->sort->qualify())
        ->handle($this->builder, $this->name, Sort::ASCENDING)->toBeTrue();

    expect($this->builder->getQuery()->orders)
        ->toBeOnlyOrder($this->builder->qualifyColumn($this->name), Sort::ASCENDING);

    expect($this->sort)
        ->isQualifying()->toBeTrue()
        ->isActive()->toBeTrue();
});
