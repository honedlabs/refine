<?php

declare(strict_types=1);

use Honed\Core\Pipes\CallsBefore;
use Honed\Refine\Refine;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->pipe = new CallsBefore();

    $this->refine = Refine::make(Product::class);
});

it('needs a before callback', function () {
    $this->pipe->instance($this->refine);

    $this->pipe->run();

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();
});

it('applies before callback', function () {
    $this->pipe->instance(
        $this->refine->before(fn ($builder) => $builder
            ->where('price', '>', 100)
        )
    );

    $this->pipe->run();

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});
