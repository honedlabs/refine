<?php

declare(strict_types=1);

namespace Honed\Refine\Pipelines;

use Honed\Refine\Refine;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 */
class AfterRefining
{
    /**
     * Apply the after refining logic.
     *
     * @param  \Honed\Refine\Refine<TModel, TBuilder>  $refine
     * @param  \Closure(Refine<TModel, TBuilder>): Refine<TModel, TBuilder>  $next
     * @return \Honed\Refine\Refine<TModel, TBuilder>
     */
    public function __invoke($refine, $next)
    {
        $after = $refine->afterRefiner();

        $refine->evaluate($after);

        return $next($refine);
    }
}
