<?php

declare(strict_types=1);

namespace Workbench\App\Refiners;

use Honed\Refine\Filters\Filter;
use Honed\Refine\Refine;
use Honed\Refine\Searches\Search;
use Honed\Refine\Sorts\AscSort;
use Honed\Refine\Sorts\DescSort;
use Honed\Refine\Sorts\Sort;
use Workbench\App\Enums\Status;
use Workbench\App\Models\Product;

/**
 * @template TModel of \App\Models\Product = \App\Models\Product
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends Refine<TModel, TBuilder>
 */
class RefineProduct extends Refine
{
    /**
     * Define the refine instance.
     *
     * @param  $this  $refine
     * @return $this
     */
    protected function definition(Refine $refine): Refine
    {
        return $refine->for(Product::class)
            ->searches([
                Search::make('name'),
                Search::make('description'),
            ])
            ->filters([
                Filter::make('name')->like(),

                Filter::make('price', 'Maximum price')
                    ->strict()
                    ->gte()
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
            ])
            ->sorts([
                DescSort::make('name', 'A-Z')
                    ->alias('name-desc')
                    ->default(),

                AscSort::make('name', 'Z-A')
                    ->alias('name-asc'),

                Sort::make('price'),

                Sort::make('best_seller', 'Favourite')
                    ->alias('favourite'),
            ]);
    }
}
