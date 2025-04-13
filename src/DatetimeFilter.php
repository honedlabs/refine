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
class DatetimeFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    protected $as = 'datetime';

    /**
     * {@inheritdoc}
     */
    public function defineType()
    {
        return Constants::DATETIME_FILTER;
    }
}
