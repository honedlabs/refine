<?php

declare(strict_types=1);

namespace Honed\Refine;

use Illuminate\Support\Arr;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filter<TModel, TBuilder>
 */
final class DataFilter extends Filter
{
    /**
     * The clauses this filter supports.
     *
     * @var array<int,\Honed\Refine\Enums\Clause>
     */
    protected $clauses = [];

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->type('data');
    }

    /**
     * Set the clauses this filter supports.
     *
     * @param  \Honed\Refine\Enums\Clause|array<int,\Honed\Refine\Enums\Clause>  ...$clauses
     * @return $this
     */
    public function clauses(...$clauses)
    {
        $clauses = Arr::flatten($clauses);

        $this->clauses = \array_merge($this->clauses, $clauses);

        return $this;
    }

    /**
     * Get the clauses this filter supports.
     *
     * @return array<int,\Honed\Refine\Enums\Clause>
     */
    public function getClauses()
    {
        return $this->clauses;
    }
}
