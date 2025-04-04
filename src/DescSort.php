<?php

declare(strict_types=1);

namespace Honed\Refine;

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
    public function setUp()
    {
        $this->type('desc');
        $this->desc();
    }
}
