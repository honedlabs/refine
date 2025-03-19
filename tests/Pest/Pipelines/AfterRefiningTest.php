<?php

declare(strict_types=1);

use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Pipelines\AfterRefining;
use Honed\Refine\Tests\Fixtures\AfterRefiningFixture;
use Honed\Refine\Refine;

beforeEach(function () {
    $this->builder = Product::query();
    $this->refine = Refine::make($this->builder);
    $this->pipe = new AfterRefining();
    $this->closure = fn ($refine) => $refine;
});

it('does not refine after', function () {
    ($this->pipe)($this->refine, $this->closure);

    expect($this->refine->getFor()->getQuery()->wheres)
        ->toBeEmpty();
});

it('refines after using property', function () {
    $refine = $this->refine->after(fn ($builder) => $builder->where('price', '>', 100));

    ($this->pipe)($refine, $this->closure);

    expect($refine->getFor()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});

it('refines after using method', function () {
    $refine = AfterRefiningFixture::make()
        ->for($this->builder);

    ($this->pipe)($refine, $this->closure);

    expect($refine->getFor()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});
