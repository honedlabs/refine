<?php

namespace Honed\Refine;

use Honed\Refine\Contracts\FromOptions;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filter<TModel, TBuilder>
 */
final class TrashedFilter extends Filter implements FromOptions
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'trashed';

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $label = 'Show deleted';

    /**
     * {@inheritdoc}
     */
    protected $type = 'trashed';

    /**
     * Create a new trashed filter instance.
     *
     * @return static
     */
    public static function new()
    {
        return resolve(self::class);
    }

    /**
     * Register the query expression to resolve the filter.
     *
     * @return \Closure(TBuilder $builder, mixed $value):TBuilder
     */
    public function queryAs()
    {
        return fn ($builder, $value) => match ($value) {
            'with' => $builder->withTrashed(),
            'only' => $builder->onlyTrashed(),
            default => $builder->withoutTrashed(),
        };
    }

    /**
     * {@inheritdoc}
     * 
     * @return array<string, string>
     */
    public function optionsFrom()
    {
        return [
            'with' => 'With deleted',
            'only' => 'Only deleted',
            'without' => 'Without deleted',
        ];
    }
}
