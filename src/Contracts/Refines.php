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
