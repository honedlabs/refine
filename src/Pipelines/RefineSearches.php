<?php

declare(strict_types=1);

namespace Honed\Refine\Pipelines;

use Honed\Core\Interpret;
use Honed\Refine\Refine;
use Illuminate\Http\Request;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 */
class RefineSearches
{
    /**
     * Apply the searches to the query.
     *
     * @param  \Honed\Refine\Refine<TModel, TBuilder>  $refine
     * @param  \Closure(Refine<TModel, TBuilder>): Refine<TModel, TBuilder>  $next
     * @return \Honed\Refine\Refine<TModel, TBuilder>
     */
    public function __invoke($refine, $next)
    {
        $request = $refine->getRequest();

        $term = $this->term($request, $refine->getSearchKey());
        $refine->term($term);

        $columns = $this->columns(
            $request,
            $refine->getMatchKey(),
            $refine->getDelimiter()
        );

        $resource = $refine->getResource();
        $applied = false;

        foreach ($this->searches($refine) as $search) {
            $active = empty($columns) ||
                \in_array($search->getParameter(), $columns);

            $applied |= $search
                ->boolean($applied ? 'or' : 'and')
                ->refine($resource, [$active, $term]);
        }

        return $next($refine);
    }

    /**
     * The searches to use.
     *
     * @param  \Honed\Refine\Refine<TModel, TBuilder>  $refine
     * @return array<int, \Honed\Refine\Search<TModel, TBuilder>>
     */
    public function searches($refine)
    {
        return $refine->getSearches();
    }

    /**
     * Get the search term from a request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @return string|null
     */
    public function term($request, $key)
    {
        $term = Interpret::string($request, $key);

        if (empty($term)) {
            return null;
        }

        return \str_replace('+', ' ', $term);
    }

    /**
     * Get the search columns from a request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @param  string  $delimiter
     * @return array<int,string>|null
     */
    public function columns($request, $key, $delimiter)
    {
        return Interpret::array($request, $key, $delimiter, 'string');

    }
}
