<?php

declare(strict_types=1);

use Honed\Refine\Pipelines\AfterRefining;
use Workbench\App\Refiners\ProductRefiner;
use Workbench\App\Refiners\UserRefiner;

beforeEach(function () {
    $this->pipe = new AfterRefining();
    $this->closure = fn ($refine) => $refine;
});

it('does not refine after', function () {
    $refiner = ProductRefiner::make();

    ($this->pipe)($refiner, $this->closure);

    expect($refiner->getResource()->getQuery()->wheres)
        ->toBeEmpty();
});

it('refines after using property', function () {
    $refiner = ProductRefiner::make();

    $refiner->after(fn ($builder) => $builder->where('price', '>', 100));

    ($this->pipe)($refiner, $this->closure);

    expect($refiner->getResource()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});

it('refines after using method', function () {
    $refiner = UserRefiner::make();

    ($this->pipe)($refiner, $this->closure);

    expect($refiner->getResource()->getQuery()->orders)
        ->toBeOnlyOrder('created_at', 'desc');
});
