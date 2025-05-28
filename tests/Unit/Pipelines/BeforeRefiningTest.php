<?php

declare(strict_types=1);

use Honed\Refine\Pipelines\BeforeRefining;
use Honed\Refine\Refine;
use Honed\Refine\Tests\Fixtures\BeforeRefiningFixture;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->refine = Refine::make($this->builder);
    $this->pipe = new BeforeRefining();
    $this->closure = fn ($refine) => $refine;
});

it('does not refine before', function () {
    $this->pipe->__invoke($this->refine, $this->closure);

    expect($this->refine->getResource()->getQuery()->wheres)
        ->toBeEmpty();
});

it('refines before using property', function () {
    $this->refine
        ->before(fn ($builder) => $builder->where('price', '>', 100));

    $this->pipe->__invoke($this->refine, $this->closure);

    expect($this->refine->getResource()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});

it('refines before using method', function () {
    $refine = BeforeRefiningFixture::make()
        ->resource($this->builder);

    $this->pipe->__invoke($refine, $this->closure);

    expect($refine->getResource()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});
