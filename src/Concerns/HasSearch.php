<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 */
trait HasSearch
{
    /**
     * Whether to use a full-text, recall search.
     *
     * @var bool
     */
    protected $fullText = false;

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
     * Add a precision search query scope to the builder.
     *
     * @param  TBuilder  $builder
     * @param  string  $value
     * @param  string  $column
     * @param  string  $boolean
     * @param  string  $operator
     * @return void
     */
    public function searchPrecision(
        $builder,
        $value,
        $column,
        $boolean = 'and',
        $operator = 'LIKE'
    ) {
        $sql = \sprintf(
            'LOWER(%s) %s ?',
            $builder->qualifyColumn($column),
            $operator
        );

        $binding = ['%'.\mb_strtolower($value, 'UTF8').'%'];

        $builder->whereRaw($sql, $binding, $boolean);
    }

    /**
     * Add a recall search query scope to the builder.
     *
     * @param  TBuilder  $builder
     * @param  string  $value
     * @param  string  $column
     * @param  string  $boolean
     * @return void
     */
    public function searchRecall($builder, $value, $column, $boolean = 'and')
    {
        $column = $builder->qualifyColumn($column);

        // @phpstan-ignore-next-line
        $builder->whereFullText($column, $value, boolean: $boolean);
    }
}
