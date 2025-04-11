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
        $request = $refine->getRequest();
        $resource = $refine->getResource();

        $scope = $refine->getScope();
        $delimiter = $refine->getDelimiter();

        foreach ($this->filters($refine) as $filter) {
            $filter->scope($scope)
                ->delimiter($delimiter)
                ->refine($resource, $request);
        }

        return $next($refine);
    }

    /**
     * The filters to use.
     *
     * @param  \Honed\Refine\Refine<TModel, TBuilder>  $refine
     * @return array<int, \Honed\Refine\Filter<TModel, TBuilder>>
     */
    public function filters($refine)
    {
        return $refine->getFilters();
    }
}
