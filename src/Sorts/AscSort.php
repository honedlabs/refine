<?php

declare(strict_types=1);

namespace Honed\Refine\Sorts;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Sorts\Sort<TModel, TBuilder>
 */
class AscSort extends Sort
{
    /**
     * Provide the instance with any necessary setup.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->ascending();
    }
}
