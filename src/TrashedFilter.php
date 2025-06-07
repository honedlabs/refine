<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Contracts\WithQuery;
use Honed\Refine\Contracts\WithOptions;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filter<TModel, TBuilder>
 */
final class TrashedFilter extends Filter implements WithOptions, WithQuery
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'trashed';

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
     * @param  TBuilder  $builder
     * @param  mixed  $value
     * @return TBuilder
     */
    public function queryUsing($builder, $value)
    {
        return match ($value) {
            'with' => $builder->withTrashed(), // @phpstan-ignore method.notFound
            'only' => $builder->onlyTrashed(), // @phpstan-ignore method.notFound
            default => $builder->withoutTrashed(), // @phpstan-ignore method.notFound
        };
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, string>
     */
    public function optionsUsing()
    {
        return [
            'with' => 'With deleted',
            'only' => 'Only deleted',
            'without' => 'Without deleted',
        ];
    }
}
