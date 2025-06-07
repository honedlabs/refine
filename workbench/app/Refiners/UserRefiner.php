<?php

declare(strict_types=1);

namespace Workbench\App\Refiners;

use Honed\Refine\Contracts\RefinesAfter;
use Honed\Refine\Contracts\RefinesBefore;
use Honed\Refine\Filter;
use Honed\Refine\Refine;
use Honed\Refine\Search;
use Workbench\App\Models\User;

/**
 * @template TModel of \Workbench\App\Models\Product
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @implements \Honed\Refine\Contracts\RefinesAfter<TModel, TBuilder>
 * @implements \Honed\Refine\Contracts\RefinesBefore<TModel, TBuilder>
 *
 * @extends \Honed\Refine\Refine<TModel, TBuilder>
 */
class UserRefiner extends Refine implements RefinesAfter, RefinesBefore
{
    /**
     * Define the resource to use for the query.
     *
     * @return TBuilder
     */
    public function resource()
    {
        return User::query();
    }

    /**
     * Define the filters available to refine the query.
     *
     * @return array<int, Filter<TModel, TBuilder>>
     */
    public function filters()
    {
        /** @var array<int, Filter<TModel, TBuilder>> */
        return [
            Filter::make('name')
                ->operator('like'),
        ];
    }

    /**
     * Define the columns to search on.
     *
     * @return array<int, Search<TModel, TBuilder>>
     */
    public function searches()
    {
        /** @var array<int, Search<TModel, TBuilder>> */
        return [
            Search::make('name'),
            Search::make('email'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterRefining($builder)
    {
        return $builder->latest();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRefining($builder)
    {
        return $builder->where('email', 'test@test.com');
    }
}
