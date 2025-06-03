<?php

declare(strict_types=1);

namespace Honed\Refine;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filter<TModel, TBuilder>
 */
class SelectFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'select';

    /**
     * {@inheritdoc}
     */
    protected $as = 'array';

    /**
     * {@inheritdoc}
     */
    protected $multiple = true;
}
