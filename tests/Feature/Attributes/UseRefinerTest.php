<?php

declare(strict_types=1);

use Honed\Refine\Attributes\UseRefine;
use Workbench\App\Models\User;
use Workbench\App\Refiners\RefineUser;

it('has attribute', function () {
    $attribute = new UseRefine(RefineUser::class);

    expect($attribute)
        ->toBeInstanceOf(UseRefine::class)
        ->refinerClass->toBe(RefineUser::class);

    expect(User::class)
        ->toHaveAttribute(UseRefine::class);
});
