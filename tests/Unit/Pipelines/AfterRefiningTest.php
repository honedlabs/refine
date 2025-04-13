<?php

declare(strict_types=1);

use Honed\Refine\Pipelines\AfterRefining;
use Honed\Refine\Refine;
use Honed\Refine\Tests\Fixtures\AfterRefiningFixture;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->pipe = new AfterRefining;
    $this->closure = fn ($refine) => $refine;

    $this->refine = Refine::make($this->builder);
});

it('does not refine after', function () {
    $this->pipe->__invoke($this->refine, $this->closure);

    expect($this->refine->getResource()->getQuery()->wheres)
        ->toBeEmpty();
});

it('refines after using property', function () {
    $refine = $this->refine->after(fn ($builder) => $builder->where('price', '>', 100));

    $this->pipe->__invoke($refine, $this->closure);

    expect($refine->getResource()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});

it('refines after using method', function () {
    $refine = AfterRefiningFixture::make()
        ->resource($this->builder);

    $this->pipe->__invoke($refine, $this->closure);

    expect($refine->getResource()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});
