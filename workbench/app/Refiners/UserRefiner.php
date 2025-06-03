<?php

declare(strict_types=1);

namespace Workbench\App\Refiners;

use Honed\Refine\Filter;
use Honed\Refine\Refine;
use Honed\Refine\Search;
use Honed\Refine\Sort;
use Honed\Refine\Tests\Stubs\Status;
use Workbench\App\Models\Product;

/**
 * @template TModel of \Workbench\App\Models\Product
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Refine<TModel, TBuilder>
 */
class UserRefiner extends Refine
{
    /**
     * Define the database resource to use.
     *
     * @return TBuilder
     */
    public function resource()
    {
        return Product::query();
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

            Filter::make('price', 'Maximum price')
                ->strict()
                ->operator('>=')
                ->options([10, 20, 50, 100]),

            Filter::make('status')
                ->strict()
                ->enum(Status::class)
                ->multiple(),

            Filter::make('status', 'Single')
                ->alias('only')
                ->enum(Status::class),

            Filter::make('best_seller', 'Favourite')
                ->asBoolean()
                ->alias('favourite'),

            Filter::make('created_at', 'Oldest')
                ->alias('oldest')
                ->asDate()
                ->operator('>='),

            Filter::make('created_at', 'Newest')
                ->alias('newest')
                ->asDate()
                ->operator('<='),
        ];
    }

    /**
     * Define the sorts available to order the records.
     *
     * @return array<int, Sort<TModel, TBuilder>>
     */
    public function sorts()
    {
        /** @var array<int, Sort<TModel, TBuilder>> */
        return [
            Sort::make('name', 'A-Z')
                ->alias('name-desc')
                ->desc()
                ->default(),

            Sort::make('name', 'Z-A')
                ->alias('name-asc')
                ->asc(),

            Sort::make('price'),

            Sort::make('best_seller', 'Favourite')
                ->alias('favourite'),
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
            Search::make('description'),
        ];
    }
}
