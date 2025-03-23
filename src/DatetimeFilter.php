<?php

declare(strict_types=1);

namespace Honed\Refine;

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
    public function setUp()
    {
        $this->datetime();
    }
}
