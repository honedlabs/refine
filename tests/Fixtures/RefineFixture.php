<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Fixtures;

use Honed\Refine\Filters\BooleanFilter;
use Honed\Refine\Filters\DateFilter;
use Honed\Refine\Filters\Filter;
use Honed\Refine\Filters\SetFilter;
use Honed\Refine\Refine;
use Honed\Refine\Searches\Search;
use Honed\Refine\Sorts\Sort;
use Honed\Refine\Tests\Stubs\Status;

class RefineFixture extends Refine
{
    public function filters()
    {
        return [
            Filter::make('name')->like(),
            SetFilter::make('price', 'Maximum price')->options([10, 20, 50, 100])->lt(),
            SetFilter::make('status')->enum(Status::class)->multiple(),
            SetFilter::make('status', 'Single')->alias('only')->enum(Status::class),
            BooleanFilter::make('best_seller', 'Favourite')->alias('favourite'),
            DateFilter::make('created_at', 'Oldest')->alias('oldest')->gt(),
            DateFilter::make('created_at', 'Newest')->alias('newest')->lt(),

        ];
    }

    public function sorts()
    {
        return [
            Sort::make('name', 'A-Z')->alias('name-desc')->desc()->default(),
            Sort::make('name', 'Z-A')->alias('name-asc')->asc(),
            Sort::make('price'),
            Sort::make('best_seller', 'Favourite')->alias('favourite'),
        ];
    }

    public function searches()
    {
        return [
            Search::make('name'),
            Search::make('description'),
        ];
    }
}
