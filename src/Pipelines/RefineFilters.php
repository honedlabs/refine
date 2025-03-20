<?php

declare(strict_types=1);

namespace Honed\Refine\Pipelines;

use Honed\Refine\Refine;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 */
class RefineFilters
{
    /**
     * Apply the filters to the query.
     *
     * @param  \Honed\Refine\Refine<TModel, TBuilder>  $refine
     * @param  \Closure(Refine<TModel, TBuilder>): Refine<TModel, TBuilder>  $next
     * @return \Honed\Refine\Refine<TModel, TBuilder>
     */
    public function __invoke($refine, $next)
    {
        if (! $refine->isFiltering()) {
            return $next($refine);
        }

        $request = $refine->getRequest();
        $for = $refine->getFor();

        $scope = $refine->getScope();
        $delimiter = $refine->getDelimiter();

        $filters = $refine->getFilters();

        foreach ($filters as $filter) {
            $filter->scope($scope)
                ->delimiter($delimiter)
                ->refine($for, $request);
        }

        return $next($refine);
    }
}
