<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Fixtures;

use Honed\Refine\Filter;
use Illuminate\Database\Query\Builder;

final class DetailFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        //
    }

    /**
     * Register the query expression to resolve the filter.
     *
     * @param  \Illuminate\Database\Query\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  mixed  $value
     * @return void
     */
    public function using(Builder $builder, $value)
    {
        $builder->whereRelation('details', 'quantity', $value);
    }
}
