<?php

declare(strict_types=1);

namespace Honed\Refine {
    /**
     * @method \Honed\Refine\Refine for(\Illuminate\Database\Eloquent\Model|class-string<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Builder $for) Set the builder instance to refine.
     * @method \Honed\Refine\Refine before(\Closure $callback) Set a closure to be called before the refiners have been applied.
     * @method \Honed\Refine\Refine after(\Closure $callback) Set a closure to be called after the refiners have been applied.
     * @method \Honed\Refine\Refine sorts(array<int, \Honed\Refine\Sorts\Sort>|\Illuminate\Support\Collection<int, \Honed\Refine\Sorts\Sort> $sorts) Merge a set of sorts with the existing sorts.
     * @method \Honed\Refine\Refine filters(array<int, \Honed\Refine\Filters\Filter>|\Illuminate\Support\Collection<int, \Honed\Refine\Filters\Filter> $filters) Merge a set of filters with the existing filters.
     * @method \Honed\Refine\Refine searches(array<int, \Honed\Refine\Searches\Search>|\Illuminate\Support\Collection<int, \Honed\Refine\Searches\Search> $searches) Merge a set of searches with the existing searches.
     */
    class Refine {}
}
