<?php

declare(strict_types=1);

namespace Honed\Refine;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends Search<TModel, TBuilder>
 */
class FullTextSearch extends Search
{
    /**
     * {@inheritdoc}
     */
    protected $fullText = true;
}
