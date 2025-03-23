<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

interface DefinesOptions
{
    /**
     * Define the options to be supplied by the refinement.
     *
     * @return class-string<\BackedEnum>|array<int|string,bool|float|int|string|null|\Honed\Refine\Option>
     */
    public function defineOptions();
}
