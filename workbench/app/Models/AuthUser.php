<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Workbench\App\Refiners\RefineUser;

class AuthUser extends User
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The refiner to use for the model.
     *
     * @var class-string<\Honed\Refine\Refine>
     */
    protected static $refiner = RefineUser::class;
}
