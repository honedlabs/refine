<?php

declare(strict_types=1);

namespace Honed\Refine {
    /**
     * @method $this for(\Illuminate\Database\Eloquent\Model|class-string<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Builder $for) Set the builder instance to refine.
     * @method $this before(\Closure $callback) Set a closure to be called before the refiners have been applied.
     * @method $this after(\Closure $callback) Set a closure to be called after the refiners have been applied.
     * @method $this sorts(array<int, \Honed\Refine\Sort>|\Illuminate\Support\Collection<int, \Honed\Refine\Sort> $sorts) Merge a set of sorts with the existing sorts.
     * @method $this filters(array<int, \Honed\Refine\Filter>|\Illuminate\Support\Collection<int, \Honed\Refine\Filter> $filters) Merge a set of filters with the existing filters.
     * @method $this searches(array<int, \Honed\Refine\Search>|\Illuminate\Support\Collection<int, \Honed\Refine\Search> $searches) Merge a set of searches with the existing searches.
     */
    class Refine {}
}
