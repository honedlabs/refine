<?php

declare(strict_types=1);

namespace Workbench\App\Refiners;

use Honed\Refine\Filters\Filter;
use Honed\Refine\Refine;
use Honed\Refine\Searches\Search;
use Honed\Refine\Sorts\Sort;
use Workbench\App\Models\User;

/**
 * @template TModel of \App\Models\User = \App\Models\User
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends Refine<TModel, TBuilder>
 */
class RefineUser extends Refine
{
    /**
     * Define the refine instance.
     *
     * @param  $this  $refine
     * @return $this
     */
    protected function definition(Refine $refine): Refine
    {
        return $refine->for(User::class)
            ->searchPlaceholder('Search users...')
            ->sortKey('order')
            ->searchKey('q')
            ->matchKey('on')
            ->matchable()
            ->searches([
                Search::make('name'),
                Search::make('email'),
            ])
            ->filters([
                Filter::make('name')->like(),
            ])
            ->sorts([
                Sort::make('name', 'Name A-Z')->asc(),
                Sort::make('name', 'Name Z-A')->desc(),
            ])
            ->after(fn ($builder) => $builder->latest())
            ->before(fn ($builder) => $builder->where('email', 'test@test.com'));
    }
}
