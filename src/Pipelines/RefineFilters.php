<?php

declare(strict_types=1);

namespace Honed\Refine\Pipelines;

use Honed\Refine\Refine;

/**
 * @template T of \Honed\Refine\Refine = \Honed\Refine\Refine
 */
class RefineFilters
{
    /**
     * Apply the filters to the query.
     *
     * @param  T  $refine
     * @param  \Closure(T): T  $next
     * @return T
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
     * @param  T  $refine
     * @return array<int, \Honed\Refine\Filter>
     */
    public function filters($refine)
    {
        return $refine->getFilters();
    }
}
