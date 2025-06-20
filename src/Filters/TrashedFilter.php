<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filters\Filter<TModel, TBuilder>
 */
class TrashedFilter extends Filter
{
    /**
     * Provide the instance with any necessary setup.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->name('trashed');

        $this->type(Filter::TRASHED);

        $this->label('Show deleted');

        $this->options([
            'with' => 'With deleted',
            'only' => 'Only deleted',
            'without' => 'Without deleted',
        ]);

        $this->query(fn ($builder, $value) => match ($value) {
            'with' => $builder->withTrashed(),
            'only' => $builder->onlyTrashed(),
            default => $builder->withoutTrashed(),
        });
    }

    /**
     * Create a new trashed filter instance.
     *
     * @return static
     */
    public static function new()
    {
        return resolve(static::class);
    }
}
