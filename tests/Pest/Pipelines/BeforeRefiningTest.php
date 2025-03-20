<?php

declare(strict_types=1);

use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Pipelines\BeforeRefining;
use Honed\Refine\Tests\Fixtures\BeforeRefiningFixture;
use Honed\Refine\Refine;

beforeEach(function () {
    $this->builder = Product::query();
    $this->refine = Refine::make($this->builder);
    $this->pipe = new BeforeRefining();
    $this->closure = fn ($refine) => $refine;
});

it('does not refine before', function () {
    $this->pipe->__invoke($this->refine, $this->closure);

    expect($this->refine->getFor()->getQuery()->wheres)
        ->toBeEmpty();
});

it('refines before using property', function () {
    $this->refine
        ->before(fn ($builder) => $builder->where('price', '>', 100));

    $this->pipe->__invoke($this->refine, $this->closure);

    expect($this->refine->getFor()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});

it('refines before using method', function () {
    $refine = BeforeRefiningFixture::make()
        ->for($this->builder);

    $this->pipe->__invoke($refine, $this->closure);

    expect($refine->getFor()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});