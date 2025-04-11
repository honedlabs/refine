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
final class TrashedFilter extends Filter
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
     * @return \Closure(TBuilder $builder, mixed $value):TBuilder
     */
    public function defineQuery()
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
     * @return array<string,string>
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
