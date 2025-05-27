<?php

namespace Honed\Refine;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Sort<TModel, TBuilder>
 */
class AscSort extends Sort
{
    /**
     * {@inheritdoc}
     */
    protected $fixed = 'asc';

    /**
     * {@inheritdoc}
     */
    protected $type = 'asc';
}
