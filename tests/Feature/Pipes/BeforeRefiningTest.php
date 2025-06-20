<?php

declare(strict_types=1);

use Honed\Refine\Pipes\BeforeRefining;
use Honed\Refine\Refine;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->pipe = new BeforeRefining();

    $this->refine = Refine::make(Product::class);
});

it('needs a before callback', function () {
    $this->pipe->run($this->refine);

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeEmpty();
});

it('applies before callback', function () {
    $this->pipe->run(
        $this->refine->before(fn ($builder) => $builder
            ->where('price', '>', 100)
        )
    );

    expect($this->refine->getBuilder()->getQuery()->wheres)
        ->toBeOnlyWhere('price', 100, '>', 'and');
});
