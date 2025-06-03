<?php

declare(strict_types=1);

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
    protected $type = 'asc';

    /**
     * {@inheritdoc}
     */
    protected $fixed = 'asc';
}
