<?php

declare(strict_types=1);

namespace Honed\Refine\Pipelines;

use Closure;
use Honed\Refine\Refine;

class AfterRefining
{
    /**
     * Apply the after refining logic.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
     *
     * @param  \Honed\Refine\Refine<TModel, TBuilder>  $refine
     * @return \Honed\Refine\Refine<TModel, TBuilder>
     */
    public function __invoke(Refine $refine, Closure $next): Refine
    {
        $after = $refine->afterRefiner();

        $refine->evaluate($after);

        return $next($refine);
    }
}
