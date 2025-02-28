<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

/**
 * @method void handle(mixed ...$parameters)
 * @method bool apply(mixed ...$parameters)
 */
interface Refines
{
    /**
     * Get the unique key to identify the refiner.
     *
     * @return string
     */
    public function getUniqueKey();
}
