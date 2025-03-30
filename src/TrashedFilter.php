<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Contracts\HasQuery;
use Honed\Refine\Contracts\DefinesOptions;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filter<TModel, TBuilder>
 */
final class TrashedFilter extends Filter implements DefinesOptions, HasQuery
{
    /**
     *  Create a new sort instance.
     *
     * @return static
     */
    public static function new()
    {
        return resolve(self::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->name('trashed');
        $this->type('trashed');
        $this->label('Show deleted');
    }

    /**
     * Register the query expression to resolve the filter.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  mixed  $value
     * @return void
     */
    public function queryAs($builder, $value)
    {
        match ($value) {
            // @phpstan-ignore-next-line
            'with' => $builder->withTrashed(),
            // @phpstan-ignore-next-line
            'only' => $builder->onlyTrashed(),
            // @phpstan-ignore-next-line
            default => $builder->withoutTrashed(),
        };
    }

    /**
     * Define the options to be supplied by the refinement.
     *
     * @return array<string,mixed>
     */
    public function defineOptions()
    {
        return [
            'with' => 'With deleted',
            'only' => 'Only deleted',
            'without' => 'Without deleted',
        ];
    }
}
