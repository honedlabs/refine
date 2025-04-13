<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Refine\Support\Constants;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends Filter<TModel, TBuilder>
 */
class BooleanFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    protected $as = 'boolean';

    /**
     * {@inheritdoc}
     */
    public function defineType()
    {
        return Constants::BOOLEAN_FILTER;
    }
}
