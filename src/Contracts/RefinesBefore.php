<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 */
interface RefinesBefore
{
    /**
     * Define a callback to be applied before the refiners have been applied.
     *
     * @param  TBuilder  $builder
     * @return void
     */
    public function beforeRefining($builder);
}
