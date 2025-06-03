<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

use BackedEnum;

interface WithOptions
{
    /**
     * Define the source you wish to create options from.
     *
     * @return class-string<BackedEnum>|array<int|string,scalar|null>|\Illuminate\Support\Collection<int|string,scalar|null>
     */
    public function optionsUsing();
}
