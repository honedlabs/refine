<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Core\Concerns\CanScope;
use Honed\Core\Concerns\HasLifecycleHooks;
use Honed\Core\Concerns\HasPipeline;
use Honed\Core\Concerns\HasRequest;
use Honed\Core\Concerns\HasResource;
use Honed\Core\Pipes\CallsAfter;
use Honed\Core\Pipes\CallsBefore;
use Honed\Persist\Concerns\Persistable;
use Honed\Persist\Facades\Persist;
use Honed\Refine\Pipes\FilterQuery;
use Honed\Refine\Pipes\PersistData;
use Honed\Refine\Pipes\SearchQuery;
use Honed\Refine\Pipes\SortQuery;
use Illuminate\Http\Request;

/**
 * @phpstan-require-implements \Honed\Core\Contracts\HooksIntoLifecycle
 * @phpstan-require-implements \Honed\Persist\Contracts\CanPersistData
 *
 * @method self persistSort(string|bool $driver = true)
 * @method self persistSortInSession()
 * @method self persistSortInCookie()
 * @method bool isPersistingSort()
 * @method \Honed\Persist\Drivers\Decorator|null getSortDriver()
 * @method self persistSearch(string|bool $driver = true)
 * @method self persistSearchInSession()
 * @method self persistSearchInCookie()
 * @method bool isPersistingSearch()
 * @method \Honed\Persist\Drivers\Decorator|null getSearchDriver()
 * @method self persistFilter(string|bool $driver = true)
 * @method self persistFilterInSession()
 * @method self persistFilterInCookie()
 * @method bool isPersistingFilter()
 * @method \Honed\Persist\Drivers\Decorator|null getFilterDriver()
 */
trait CanRefine
{
    use CanScope;
    use HasDelimiter;
    use HasFilters;
    use HasLifecycleHooks;
    use HasPipeline;
    use HasRequest {
        request as setRequest;
    }
    use HasResource;
    use HasSearches;
    use HasSearchTerm;
    use HasSorts;
    use Persistable {
        __call as persistCall;
    }
    // use CanHaveSearchPlaceholder;

    /**
     * Define the names of persistable properties.
     *
     * @return array<int, string>
     */
    public function persist(): array
    {
        return [
            'sort',
            'search',
            'filter',
        ];
    }

    /**
     * Set the request instance.
     *
     * @return $this
     */
    public function request(Request $request): static
    {
        Persist::request($request);

        return $this->setRequest($request);
    }

    /**
     * Get the refined data.
     *
     * @return array<string, mixed>
     */
    public function refineToArray(): array
    {
        return [
            'sort' => $this->isSortable() ? $this->getSortKey() : null,
            'search' => $this->isSearchable() ? $this->getSearchKey() : null,
            'match' => $this->isMatchable() ? $this->getMatchKey() : null,
            'term' => $this->getSearchTerm(),
            'placeholder' => $this->getSearchPlaceholder(),
            'delimiter' => $this->getDelimiter(),
            'sorts' => $this->sortsToArray(),
            'filters' => $this->filtersToArray(),
            'searches' => $this->searchesToArray(),
        ];
    }

    /**
     * Get the pipes to be used for refining.
     *
     * @return array<int, class-string<\Honed\Core\Pipe<self>>>
     */
    protected function pipes(): array
    {
        // @phpstan-ignore-next-line
        return [
            CallsBefore::class,
            SearchQuery::class,
            FilterQuery::class,
            SortQuery::class,
            CallsAfter::class,
            PersistData::class,
        ];
    }
}
