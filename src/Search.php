<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Refine\Concerns\HasSearch;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends Refiner<TModel, TBuilder>
 */
class Search extends Refiner
{
    /**
     * @use HasSearch<TModel, TBuilder>
     */
    use HasSearch;

    /**
     * The query boolean to use for the search.
     *
     * @var 'and'|'or'
     */
    protected $boolean = 'and';

    /**
     * Set the query boolean to use for the search.
     *
     * @param  'and'|'or'  $boolean
     * @return $this
     */
    public function boolean($boolean)
    {
        $this->boolean = $boolean;

        return $this;
    }

    /**
     * Get the query boolean.
     *
     * @return 'and'|'or'
     */
    public function getBoolean()
    {
        return $this->boolean;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->type('search');
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings($value)
    {
        return \array_merge(parent::getBindings($value), [
            'boolean' => $this->getBoolean(),
        ]);
    }

    /**
     * Add the search query scope to the builder.
     *
     * @param  TBuilder  $builder
     * @param  string  $value
     * @param  string  $column
     * @param  string  $boolean
     * @return void
     */
    public function defaultQuery($builder, $value, $column, $boolean = 'and')
    {
        if ($this->isFullText()) {
            $this->searchRecall($builder, $value, $column, $boolean);

            return;
        }

        $this->searchPrecision($builder, $value, $column, $boolean);
    }
}
