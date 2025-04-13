<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Refine\Support\Constants;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends Sort<TModel, TBuilder>
 */
class DescSort extends Sort
{
    /**
     * {@inheritdoc}
     */
    protected $fixed = Constants::DESCENDING;

    /**
     * {@inheritdoc}
     */
    public function defineType()
    {
        return Constants::DESCENDING;
    }
}
