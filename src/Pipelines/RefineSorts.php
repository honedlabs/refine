<?php

declare(strict_types=1);

namespace Honed\Refine\Pipelines;

use Honed\Core\Interpret;

/**
 * @template T of \Honed\Refine\Refine = \Honed\Refine\Refine
 */
class RefineSorts
{
    /**
     * Apply the sorts refining logic.
     *
     * @param  T  $refine
     * @param  \Closure(T): T  $next
     * @return T
     */
    public function __invoke($refine, $next)
    {
        $resource = $refine->getResource();

        $value = $this->nameAndDirection(
            $refine->getRequest(),
            $refine->getSortKey()
        );

        $applied = false;

        foreach ($this->sorts($refine) as $sort) {
            $applied |= $sort->refine($resource, $value);
        }

        if (! $applied && $sort = $refine->getDefaultSort()) {
            [$_, $direction] = $value;

            $value = [$sort->getParameter(), $direction];

            $sort->refine($resource, $value);
        }

        return $next($refine);
    }

    /**
     * The sorts to use.
     *
     * @param  T  $refine
     * @return array<int, \Honed\Refine\Sort>
     */
    public function sorts($refine)
    {
        return $refine->getSorts();
    }

    /**
     * Get the sort name and direction from a request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @return array{string|null, 'asc'|'desc'|null}
     */
    public function nameAndDirection($request, $key)
    {
        $sort = Interpret::string($request, $key);

        if (empty($sort)) {
            return [null, null];
        }

        if (\str_starts_with($sort, '-')) {
            return [\substr($sort, 1), 'desc'];
        }

        return [$sort, 'asc'];
    }
}
