<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Illuminate\Http\Request;
use Honed\Refine\Searches\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Arrayable;

trait HasSearch
{
    const SearchKey = 'search';

    const ColumnsKey = 'searches';

    /**
     * Whether the search should consider the presence of the columns key in the request.
     *
     * @var bool
     */
    protected $searchAll = false;

    /**
     * An array of the attributes to be used for searching.
     *
     * @var array<int,\Honed\Refine\Searches\Search>|null
     */
    protected $searches;

    /**
     * The query parameter key to look for in the request for the search value.
     *
     * @var string
     */
    protected $searchKey = self::SearchKey;

    /**
     * The query parameter key to look for in the request for the columns to be used for searching.
     *
     * @var string
     */
    protected $columnsKey = self::ColumnsKey;

    /**
     * Define new columns to be used for searching.
     *
     * @param  iterable<\Honed\Refine\Searches\Search>  $searches
     * @return $this
     */
    public function addSearches(iterable $searches): static
    {
        if ($searches instanceof Arrayable) {
            $searches = $searches->toArray();
        }

        /**
         * @var array<int, \Honed\Refine\Searches\Search>
         */
        $searches = type($searches)->asArray();

        $this->searches = \array_merge($this->searches ?? [], $searches);

        return $this;
    }

    /**
     * @return $this
     */
    public function addSearch(Search $search): static
    {
        $this->searches[] = $search;

        return $this;
    }

    /**
     * Retrieve the columns to be used for searching.
     *
     * @return array<int,\Honed\Refine\Searches\Search>
     */
    public function getSearches(): array
    {
        return $this->searches ??= match (true) {
            \method_exists($this, 'searches') => $this->searches(),
            default => [],
        };
    }

    /**
     * Determines if the instance has any searches.
     */
    public function hasSearch(): bool
    {
        return ! empty($this->getSearches());
    }

    /**
     * Search the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return $this
     */
    public function search(Builder $builder, Request $request): static
    {
        $columns = $this->getSearchesFromRequest($request);

        $applied = false;
        foreach ($this->getSearches() as $search) {
            $applied |= $search->apply(
                builder: $builder,
                request: $request,
                searchKey: $this->getSearchKey(),
                and: ! $applied,
                columns: $this->searchAll ? true : $columns,
            );
        }

        return $this;
    }

    /**
     * Retrieve the columns to be used for searching from the request.
     *
     * @return array<int,string>|true
     */
    public function getSearchesFromRequest(Request $request): array|true
    {
        $value = $request->string($this->getColumnsKey())->toString();

        if (empty($value)) {
            return true;
        }

        return \array_map(
            fn ($v) => \trim($v),
            \explode(',', (string) $value)
        );
    }

    /**
     * Sets the search key to look for in the request.
     *
     * @return $this
     */
    public function searchKey(string $searchKey): static
    {
        $this->searchKey = $searchKey;

        return $this;
    }

    /**
     * Gets the search key to look for in the request.
     */
    public function getSearchKey(): string
    {
        return $this->searchKey;
    }

    /**
     * Sets the columns key to look for in the request.
     *
     * @return $this
     */
    public function columnsKey(string $columnsKey): static
    {
        $this->columnsKey = $columnsKey;

        return $this;
    }

    /**
     * Gets the columns key to look for in the request.
     */
    public function getColumnsKey(): string
    {
        return $this->columnsKey;
    }
}
