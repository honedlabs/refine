<?php

declare(strict_types=1);

use Honed\Refine\Attributes\UseRefiner;
use Workbench\App\Models\User;
use Workbench\App\Refiners\UserRefiner;

it('has attribute', function () {
    $attribute = new UseRefiner(UserRefiner::class);

    expect($attribute)
        ->toBeInstanceOf(UseRefiner::class)
        ->refinerClass->toBe(UserRefiner::class);

    expect(User::class)
        ->toHaveAttribute(UseRefiner::class);
});
