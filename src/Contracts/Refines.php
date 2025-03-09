<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

/**
 * @method bool refine(mixed ...$parameters)
 * @method void apply(mixed ...$parameters)
 */
interface Refines
{
    /**
     * Get the parameter for the refiner.
     *
     * @return string
     */
    public function getParameter();

    /**
     * Determine if the refiner is currently being applied.
     *
     * @return bool
     */
    public function isActive();
}
