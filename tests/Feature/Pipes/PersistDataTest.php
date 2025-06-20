<?php

declare(strict_types=1);

use Honed\Refine\Pipes\PersistData;
use Honed\Refine\Refine;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->pipe = new PersistData();

    $this->refine = Refine::make(User::class);
});

it('persists data to the stores', function () {

    $this->pipe->run($this->refine);
});
