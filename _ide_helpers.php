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

    /**
     * @method $this oldest(string $column = null) Add an `order by` clause to the query.
     * @method $this newest(string $column = null) Add an `order by` clause to the query.
     * @method $this orderBy(string $column, string $direction = 'asc') Add an `order by` clause to the query.
     */
    class Sort {}

    /**
     * @method $this where(string|\Illuminate\Contracts\Database\Query\Expression $column, mixed $operator, mixed $value) Add a `where` clause to the query.
     * @method $this whereNot(string|\Illuminate\Contracts\Database\Query\Expression $column, mixed $operator, mixed $value) Add a `where not` clause to the query.
     * @method $this whereIn(string|\Illuminate\Contracts\Database\Query\Expression $column, mixed $values) Add a `where in` clause to the query.
     * @method $this whereNotIn(string|\Illuminate\Contracts\Database\Query\Expression $column, mixed $values) Add a `where not in` clause to the query.
     * @method $this whereNull(string|\Illuminate\Contracts\Database\Query\Expression $column) Add a `where null` clause to the query.
     * @method $this whereNotNull(string|\Illuminate\Contracts\Database\Query\Expression $column) Add a `where not null` clause to the query.
     * @method $this whereBetween(string|\Illuminate\Contracts\Database\Query\Expression $column, mixed $values) Add a `where between` clause to the query.
     * @method $this whereNotBetween(string|\Illuminate\Contracts\Database\Query\Expression $column, mixed $values) Add a `where not between` clause to the query.
     * @method $this whereRaw(string $sql, array $bindings = []) Add a `where raw` clause to the query.
     * @method $this whereDate(string|\Illuminate\Contracts\Database\Query\Expression $column, string $operator, \Carbon\Carbon $value) Add a `where date` clause to the query.
     * @method $this whereMonth(string|\Illuminate\Contracts\Database\Query\Expression $column, string $operator, \Carbon\Carbon $value) Add a `where month` clause to the query.
     * @method $this whereYear(string|\Illuminate\Contracts\Database\Query\Expression $column, string $operator, \Carbon\Carbon $value) Add a `where year` clause to the query.
     * @method $this whereTime(string|\Illuminate\Contracts\Database\Query\Expression $column, string $operator, \Carbon\Carbon $value) Add a `where time` clause to the query.
     * @method $this whereDay(string|\Illuminate\Contracts\Database\Query\Expression $column, string $operator, \Carbon\Carbon $value) Add a `where day` clause to the query.
     * @method $this whereRelation(string $relation, string $column, string $operator, mixed $value) Add a `where relation` clause to the query.
     */
    class Filter {}

}
