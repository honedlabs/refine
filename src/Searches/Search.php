<?php

declare(strict_types=1);

namespace Honed\Refine\Searches;

use Honed\Refine\Refiner;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Refiner<TModel, TBuilder>
 */
class Search extends Refiner
{
    /**
     * Whether to use a full-text, recall search.
     *
     * @var bool
     */
    protected $fullText = false;

    /**
     * Provide the instance with any necessary setup.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->definition($this);
    }

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
     * Set whether to use a full-text search.
     *
     * @param  bool  $fullText
     * @return $this
     */
    public function fullText($fullText = true)
    {
        $this->fullText = $fullText;

        return $this;
    }

    /**
     * Determine if the search is a full-text search.
     *
     * @return bool
     */
    public function isFullText()
    {
        return $this->fullText;
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

        if (! $this->isActive() || ! $term) {
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
     * Define the search instance.
     *
     * @param  $this  $search
     * @return $this
     */
    protected function definition(self $search): self
    {
        return $search;
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
