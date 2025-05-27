<?php

namespace Honed\Refine;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filter<TModel, TBuilder>
 */
class DatetimeFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    protected $as = 'datetime';

    /**
     * {@inheritdoc}
     */
    protected $type = 'datetime';
}
