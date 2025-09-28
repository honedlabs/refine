<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filters\TernaryFilter<TModel, TBuilder>
 */
class TrashedFilter extends TernaryFilter
{
    /**
     * Provide the instance with any necessary setup.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('trashed');

        $this->label(__('refine::filters.trashed.label'));

        $this->trueLabel(__('refine::filters.trashed.true'));

        $this->falseLabel(__('refine::filters.trashed.false'));

        $this->blankLabel(__('refine::filters.trashed.blank'));

        $this->queries(
            true: fn ($builder) => $builder->withTrashed(), // @phpstan-ignore-line method.notFound
            false: fn ($builder) => $builder->onlyTrashed(), // @phpstan-ignore-line method.notFound
            blank: fn ($builder) => $builder->withoutTrashed(), // @phpstan-ignore-line method.notFound
        );
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
