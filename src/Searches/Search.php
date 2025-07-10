<?php

declare(strict_types=1);

namespace Honed\Refine\Searches;

use Honed\Refine\Refiner;
use Honed\Refine\Searches\Concerns\CanBeFullText;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Refiner<TModel, TBuilder>
 */
class Search extends Refiner
{
    use CanBeFullText;

    /**
     * The identifier to use for evaluation.
     *
     * @var string
     */
    protected $evaluationIdentifier = 'search';

    /**
     * Perform a wildcard search on the query.
     *
     * @param  TBuilder  $query
     * @param  string  $term
     * @param  string  $column
     * @param  string  $boolean
     * @param  string  $operator
     * @return void
     */
    public static function searchWildcard(
        $query,
        $term,
        $column,
        $boolean = 'and',
        $operator = 'LIKE'
    ) {
        $sql = sprintf(
            'LOWER(%s) %s ?',
            $column,
            $operator
        );

        $binding = ['%'.mb_strtolower($term, 'UTF8').'%'];

        $query->whereRaw($sql, $binding, $boolean);
    }

    /**
     * Handle the searching of the query.
     *
     * @param  TBuilder  $query
     * @param  string|null  $term
     * @param  array<int, string>|null  $columns
     * @param  bool  $or
     * @return bool
     */
    public function handle($query, $term, $columns, $or = false)
    {
        $this->checkIfActive($columns);

        if ($this->isNotActive() || ! $term) {
            return false;
        }

        return $this->refine($query, [
            ...$this->getBindings($query),
            'boolean' => $or ? 'or' : 'and',
            'search' => $term,
            'term' => $term,
        ]);
    }

    /**
     * Add a search scope to the query.
     *
     * @param  TBuilder  $query
     * @param  string  $term
     * @param  string  $column
     * @param  string  $boolean
     * @return void
     */
    public function apply($query, $term, $column, $boolean)
    {
        match (true) {
            $this->isFullText() => $query->whereFullText($column, $term, boolean: $boolean),
            default => static::searchWildcard($query, $term, $column, $boolean),
        };
    }

    /**
     * Determine if the search is active.
     *
     * @param  array<int, string>|null  $columns
     * @return void
     */
    protected function checkIfActive($columns)
    {
        $this->active(
            (! $columns) ?: in_array($this->getParameter(), $columns, true),
        );
    }
}
