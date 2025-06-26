<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Closure;
use Honed\Core\Concerns\HasPipeline;
use Honed\Core\Concerns\HasRequest;
use Honed\Core\Concerns\HasScope;
use Honed\Refine\Filters\Concerns\HasFilters;
use Honed\Refine\Pipes\AfterRefining;
use Honed\Refine\Pipes\BeforeRefining;
use Honed\Refine\Pipes\FilterQuery;
use Honed\Refine\Pipes\PersistData;
use Honed\Refine\Pipes\SearchQuery;
use Honed\Refine\Pipes\SortQuery;
use Honed\Refine\Searches\Concerns\HasSearches;
use Honed\Refine\Sorts\Concerns\HasSorts;
use Honed\Refine\Stores\CookieStore;
use Honed\Refine\Stores\SessionStore;

/**
 * @phpstan-require-implements \Honed\Refine\Contracts\RefinesData
 */
trait CanBeRefined
{
    use CanBePersisted;
    use HasDelimiter;
    use HasFilters;
    use HasPipeline;
    use HasRequest;
    use HasResource;
    use HasScope;
    use HasSearches;
    use HasSorts;

    /**
     * The callback to be processed before the refiners.
     *
     * @var Closure|null
     */
    protected $before = null;

    /**
     * The callback to be processed after refinement.
     *
     * @var Closure|null
     */
    protected $after = null;

    /**
     * The store to use for persisting search data.
     *
     * @var bool|string|null
     */
    protected $persistSearch = null;

    /**
     * The store to use for persisting filter data.
     *
     * @var bool|string|null
     */
    protected $persistFilter = null;

    /**
     * The store to use for persisting sort data.
     *
     * @var bool|string|null
     */
    protected $persistSort = null;

    /**
     * Register the callback to be executed before the refiners.
     *
     * @param  Closure|null  $callback
     * @return $this
     */
    public function before($callback)
    {
        $this->before = $callback;

        return $this;
    }

    /**
     * Get the callback to be executed before refinement.
     *
     * @return Closure|null
     */
    public function getBeforeCallback()
    {
        return $this->before;
    }

    /**
     * Register the callback to be executed after refinement.
     *
     * @param  Closure|null  $callback
     * @return $this
     */
    public function after($callback)
    {
        $this->after = $callback;

        return $this;
    }

    /**
     * Get the callback to be executed after refinement.
     *
     * @return Closure|null
     */
    public function getAfterCallback()
    {
        return $this->after;
    }

    /**
     * Set the store to use for persisting searches.
     *
     * @param  bool|string|null  $store
     * @return $this
     */
    public function persistSearch($store = true)
    {
        $this->persistSearch = $store;

        return $this;
    }

    /**
     * Set the session store to be used for persisting searches.
     *
     * @return $this
     */
    public function persistSearchInSession()
    {
        return $this->persistSearch(SessionStore::NAME);
    }

    /**
     * Set the cookie store to be used for persisting searches.
     *
     * @return $this
     */
    public function persistSearchInCookie()
    {
        return $this->persistSearch(CookieStore::NAME);
    }

    /**
     * Determine if the search should be persisted.
     *
     * @return bool
     */
    public function shouldPersistSearch()
    {
        return (bool) $this->persistSearch;
    }

    /**
     * Get the store to use for persisting searches.
     *
     * @return \Honed\Refine\Stores\Store|null
     */
    public function getSearchStore()
    {
        return $this->getStore($this->persistSearch);
    }

    /**
     * Set the store to use for persisting filters.
     *
     * @param  bool|string|null  $store
     * @return $this
     */
    public function persistFilter($store = true)
    {
        $this->persistFilter = $store;

        return $this;
    }

    /**
     * Set the session store to be used for persisting filters.
     *
     * @return $this
     */
    public function persistFilterInSession()
    {
        return $this->persistFilter(SessionStore::NAME);
    }

    /**
     * Set the cookie store to be used for persisting filters.
     *
     * @return $this
     */
    public function persistFilterInCookie()
    {
        return $this->persistFilter(CookieStore::NAME);
    }

    /**
     * Determine if the filter should be persisted.
     *
     * @return bool
     */
    public function shouldPersistFilter()
    {
        return (bool) $this->persistFilter;
    }

    /**
     * Get the store to use for persisting filters.
     *
     * @return \Honed\Refine\Stores\Store|null
     */
    public function getFilterStore()
    {
        return $this->getStore($this->persistFilter);
    }

    /**
     * Set the store to use for persisting sorts.
     *
     * @param  bool|string|null  $store
     * @return $this
     */
    public function persistSort($store = true)
    {
        $this->persistSort = $store;

        return $this;
    }

    /**
     * Set the session store to be used for persisting sorts.
     *
     * @return $this
     */
    public function persistSortInSession()
    {
        return $this->persistSort(SessionStore::NAME);
    }

    /**
     * Set the cookie store to be used for persisting sorts.
     *
     * @return $this
     */
    public function persistSortInCookie()
    {
        return $this->persistSort(CookieStore::NAME);
    }

    /**
     * Determine if the sort should be persisted.
     *
     * @return bool
     */
    public function shouldPersistSort()
    {
        return (bool) $this->persistSort;
    }

    /**
     * Get the store to use for persisting sorts.
     *
     * @return \Honed\Refine\Stores\Store|null
     */
    public function getSortStore()
    {
        return $this->getStore($this->persistSort);
    }

    /**
     * Get the refined data.
     *
     * @return array<string, mixed>
     */
    public function refineToArray()
    {
        return [
            'sort' => $this->isSortable() ? $this->getSortKey() : null,
            'search' => $this->isSearchable() ? $this->getSearchKey() : null,
            'match' => $this->isMatchable() ? $this->getMatchKey() : null,
            'term' => $this->getTerm(),
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
     * @return array<int,class-string<\Honed\Core\Pipe<\Honed\Refine\Contracts\RefinesData>>>
     */
    protected function pipes()
    {
        return [
            BeforeRefining::class,
            SearchQuery::class,
            FilterQuery::class,
            SortQuery::class,
            AfterRefining::class,
            PersistData::class,
        ];
    }
}
