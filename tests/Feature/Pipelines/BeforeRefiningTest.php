<?php

declare(strict_types=1);

use Honed\Refine\Pipelines\BeforeRefining;
use Workbench\App\Refiners\ProductRefiner;
use Workbench\App\Refiners\UserRefiner;

beforeEach(function () {
    $this->pipe = new BeforeRefining();
    $this->closure = fn ($refine) => $refine;
});

it('does not refine before', function () {
    $refiner = ProductRefiner::make();

    ($this->pipe)($refiner, $this->closure);

    expect($refiner->getResource()->getQuery()->wheres)
        ->toBeEmpty();
});

it('refines before using property', function () {
    $refiner = ProductRefiner::make();

    $refiner->before(fn ($builder) => $builder->where('price', '>', 100));

    ($this->pipe)($refiner, $this->closure);

    expect($refiner->getResource()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});

it('refines before using method', function () {
    $refiner = UserRefiner::make();

    ($this->pipe)($refiner, $this->closure);

    expect($refiner->getResource()->getQuery()->wheres)
        ->toBeOnlyWhere('email', 'test@test.com', '=', 'and');
});
