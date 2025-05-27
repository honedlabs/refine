<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 */
interface RefinesAfter
{
    /**
     * Define a callback to be applied after the refiners have been applied.
     *
     * @param  TBuilder  $builder
     * @return void
     */
    public function afterRefining($builder);
}
