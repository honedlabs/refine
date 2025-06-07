<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

use BackedEnum;

interface WithOptions
{
    /**
     * Provide the options for the filter.
     *
     * @return class-string<BackedEnum>|array<int|string,scalar|null>|\Illuminate\Support\Collection<int|string,scalar|null>
     */
    public function optionsUsing();
}
