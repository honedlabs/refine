<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Fixtures;

use Honed\Refine\Filter;
use Honed\Refine\Refine;
use Honed\Refine\Search;
use Honed\Refine\Sort;
use Honed\Refine\Tests\Stubs\Status;

class RefineFixture extends Refine
{
    /**
     * {@inheritdoc}
     */
    public function defineFilters()
    {
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
     * {@inheritdoc}
     */
    public function defineSorts()
    {
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
     * {@inheritdoc}
     */
    public function defineSearches()
    {
        return [
            Search::make('name'),
            Search::make('description'),
        ];
    }
}
