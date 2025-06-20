<?php

declare(strict_types=1);

use Honed\Refine\Pipes\AfterRefining;
use Honed\Refine\Refine;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->pipe = new AfterRefining();

    $this->refine = Refine::make(Product::class);
});

it('needs an after callback', function () {
    $this->pipe->run($this->refine);

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();
});

it('applies after callback', function () {
    $this->pipe->run(
        $this->refine
            ->after(fn ($builder) => $builder
                ->where('price', '>', 100)
            )
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});
