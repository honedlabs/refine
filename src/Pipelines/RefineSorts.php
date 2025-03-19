<?php

declare(strict_types=1);

namespace Honed\Refine\Pipelines;

use Closure;
use Honed\Core\Interpret;
use Honed\Refine\Refine;
use Illuminate\Http\Request;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 */
class RefineSorts
{
    /**
     * Apply the sorts refining logic.
     *
     * @param  \Honed\Refine\Refine<TModel, TBuilder>  $refine
     * @return \Honed\Refine\Refine<TModel, TBuilder>
     */
    public function __invoke(Refine $refine, Closure $next): Refine
    {
        if (! $refine->isSorting()) {
            return $next($refine);
        }

        $request = $refine->getRequest();
        $for = $refine->getFor();

        $sortsKey = $refine->formatScope($refine->getSortsKey());

        $value = $this->nameAndDirection($request, $sortsKey);

        $applied = false;

        foreach ($refine->getSorts() as $sort) {
            $applied |= $sort->refine($for, $value);
        }

        if (! $applied && $sort = $refine->getDefaultSort()) {
            [$_, $direction] = $value;

            $value = [$sort->getParameter(), $direction];

            $sort->refine($for, $value);
        }

        return $next($refine);
    }

    /**
     * Get the sort name and direction from a request.
     *
     * @return array{string|null, 'asc'|'desc'|null}
     */
    public function nameAndDirection(Request $request, string $key): array
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
