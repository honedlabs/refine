<?php

declare(strict_types=1);

use Honed\Refine\Pipes\PersistData;
use Honed\Refine\Refine;
use Illuminate\Support\Facades\Session;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->pipe = new PersistData();

    $this->refine = Refine::make(User::class)
        ->persistSearchInSession();
});

it('does not persist data to the stores if no data', function () {
    $this->pipe->run($this->refine);

    expect(Session::get($this->refine->getPersistKey()))
        ->toBeNull();
});

it('persists data to stores', function () {
    $this->refine->getSearchStore()
        ->put([
            'search' => [
                'term' => 'test',
                'cols' => ['name', 'description'],
            ],
        ]);

    $this->pipe->run($this->refine);

    expect(Session::get($this->refine->getPersistKey()))
        ->toBeArray()
        ->toHaveCount(1)
        ->toHaveKey('search')
        ->{'search'}
        ->scoped(fn ($search) => $search
            ->toBeArray()
            ->toHaveKeys(['term', 'cols'])
            ->{'term'}->toBe('test')
            ->{'cols'}->toEqual(['name', 'description'])
        );
});
