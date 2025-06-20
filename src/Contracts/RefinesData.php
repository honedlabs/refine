<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

use Closure;

/**
 * @phpstan-require-extends \Honed\Core\Primitive
 */
interface RefinesData extends FiltersData, SearchesData, SortsData
{
    /**
     * Get the callback to be executed before the refiners.
     *
     * @return Closure|null
     */
    public function getBeforeCallback();

    /**
     * Get the callback to be executed after refinement.
     *
     * @return Closure|null
     */
    public function getAfterCallback();
}
