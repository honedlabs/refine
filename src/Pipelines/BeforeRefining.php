<?php

declare(strict_types=1);

namespace Honed\Refine\Pipelines;

use Closure;

/**
 * @template T of \Honed\Refine\Refine = \Honed\Refine\Refine
 */
class BeforeRefining
{
    /**
     * Apply the before refining logic.
     *
     * @param  T  $refine
     * @param  Closure(T): T  $next
     * @return T
     */
    public function __invoke($refine, $next)
    {
        $before = $refine->getBeforeCallback();

        $refine->evaluate($before);

        return $next($refine);
    }
}
