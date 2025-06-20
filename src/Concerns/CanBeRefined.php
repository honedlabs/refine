<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Closure;
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
use Illuminate\Support\Facades\Pipeline;

/**
 * @phpstan-require-implements \Honed\Refine\Contracts\RefinesData
 */
trait CanBeRefined
{
    use CanBePersisted;
    use HasDelimiter;
    use HasFilters;
    use HasRequest;
    use HasResource;
    use HasScope;
    use HasSearches;
    use HasSorts;

    /**
     * Indicate whether the refinements have been processed.
     *
     * @var bool
     */
    protected $refined = false;

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
     * Determine if the refinements have been processed.
     *
     * @return bool
     */
    public function isRefined()
    {
        return $this->refined;
    }

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
     * Refine the provided resource.
     *
     * @return $this
     */
    public function refine()
    {
        if ($this->isRefined()) {
            return $this;
        }

        Pipeline::send($this)
            ->through($this->pipes())
            ->thenReturn();

        $this->refined = true;

        return $this;
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
            'sort' => $this->getSortKey(),
            'search' => $this->getSearchKey(),
            'match' => $this->getMatchKey(),
            'term' => $this->getTerm(),
            'delimiter' => $this->getDelimiter(),
            'placeholder' => $this->getSearchPlaceholder(),
            'sorts' => $this->sortsToArray(),
            'filters' => $this->filtersToArray(),
            'searches' => $this->searchesToArray(),
        ];
    }

    /**
     * Get the pipes to be used for refining.
     *
     * @return array<int,class-string<\Honed\Refine\Pipes\Pipe>>
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

    // /**
    //  * Apply the searches to the resource.
    //  *
    //  * @return void
    //  */
    // protected function search()
    // {
    //     $builder = $this->getBuilder();

    //     [$persistedTerm, $persistedColumns] = $this->getPersistedSearchValue();

    //     $this->term = $this->getSearchValue($this->request) ?? $persistedTerm;

    //     $columns = $this->getSearchColumns($this->request) ?? $persistedColumns;

    //     $this->persistSearchValue($this->term, $columns);

    //     if ($this->isScout()) {
    //         $model = $this->getModel();

    //         $builder->whereIn(
    //             $builder->qualifyColumn($model->getKeyName()),
    //             // @phpstan-ignore-next-line method.notFound
    //             $model->search($this->term)->keys()
    //         );

    //         return;
    //     }

    //     $or = false;

    //     foreach ($this->getSearches() as $search) {
    //         $or = $search->handle(
    //             $builder, $this->term, $columns, $or
    //         ) || $or;
    //     }
    // }

    // /**
    //  * Apply the filters to the resource.
    //  *
    //  * @return void
    //  */
    // protected function filter()
    // {
    //     $builder = $this->getBuilder();

    //     $applied = false;

    //     foreach ($this->getFilters() as $filter) {
    //         $value = $this->getFilterValue($this->request, $filter);

    //         $applied = $filter->handle($builder, $value) || $applied;

    //         $this->persistFilterValue($filter, $value);
    //     }

    //     if ($applied) {
    //         return;
    //     }

    //     foreach ($this->getFilters() as $filter) {
    //         $value = $this->getPersistedFilterValue($filter);

    //         $filter->handle($builder, $value);

    //         $this->persistFilterValue($filter, $value);
    //     }
    // }

    // /**
    //  * Apply the sorts to the resource.
    //  *
    //  * @return void
    //  */
    // protected function sort()
    // {
    //     $builder = $this->getBuilder();

    //     [$parameter, $direction] = $this->getSortValue($this->request);

    //     if (! $parameter) {
    //         [$parameter, $direction] = $this->getPersistedSortValue();
    //     }

    //     $this->persistSortValue($parameter, $direction);

    //     $applied = false;

    //     foreach ($this->getSorts() as $sort) {
    //         $applied = $sort->handle(
    //             $builder, $parameter, $direction
    //         ) || $applied;
    //     }

    //     if (! $applied && $sort = $this->getDefaultSort()) {
    //         $parameter = $sort->getParameter();

    //         $sort->handle($builder, $parameter, $direction);

    //         return;
    //     }
    // }
}
