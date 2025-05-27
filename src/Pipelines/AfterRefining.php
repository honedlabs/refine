<?php

declare(strict_types=1);

namespace Honed\Refine\Pipelines;

/**
 * @template T of \Honed\Refine\Refine = \Honed\Refine\Refine
 */
class AfterRefining
{
    /**
     * Apply the after refining logic.
     *
     * @param  T  $refine
     * @param  \Closure(T): T  $next
     * @return T
     */
    public function __invoke($refine, $next)
    {
        $after = $refine->getAfterCallback();

        $refine->evaluate($after);

        return $next($refine);
    }
}
