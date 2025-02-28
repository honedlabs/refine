<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Searches\Search;
use Illuminate\Support\Collection;

trait HasSearches
{
    use AccessesRequest;

    /**
     * The query parameter to identify the search string.
     *
     * @var string|null
     */
    protected $searchesKey;

    /**
     * Whether the search columns can be toggled.
     *
     * @var bool|null
     */
    protected $match;

    /**
     * The query parameter to identify the columns to search on.
     *
     * @var string|null
     */
    protected $matchesKey;

    /**
     * The search term as a string without replacements.
     *
     * @var string|null
     */
    protected $term;

    /**
     * List of the searches.
     *
     * @var array<int,\Honed\Refine\Searches\Search>|null
     */
    protected $searches;

    /**
     * Set the query parameter to identify the search string.
     *
     * @param  string  $searchesKey
     * @return $this
     */
    public function searchesKey($searchesKey)
    {
        $this->searchesKey = $searchesKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the search string.
     *
     * @return string
     */
    public function getSearchesKey()
    {
        if (isset($this->searchesKey)) {
            return $this->searchesKey;
        }

        return $this->getFallbackSearchesKey();
    }

    /**
     * Get the fallback query parameter to identify the search string.
     *
     * @return string
     */
    protected function getFallbackSearchesKey()
    {
        return type(config('refine.config.searches', 'search'))->asString();
    }

    /**
     * Get the query parameter to identify the columns to search on.
     *
     * @return string
     */
    public function getMatchesKey()
    {
        if (isset($this->matchesKey)) {
            return $this->matchesKey;
        }

        return $this->getFallbackMatchesKey();
    }

    /**
     * Get the fallback query parameter to identify the columns to search on.
     *
     * @return string
     */
    protected function getFallbackMatchesKey()
    {
        return type(config('refine.config.matches', 'match'))->asString();
    }

    /**
     * Set whether the search columns can be toggled.
     *
     * @param  bool|null  $match
     * @return $this
     */
    public function match($match = true)
    {
        $this->match = $match;

        return $this;
    }

    /**
     * Determine whether the search columns can be toggled.
     *
     * @return bool
     */
    public function canMatch()
    {
        if (isset($this->match)) {
            return $this->match;
        }

        return $this->getFallbackCanMatch();
    }

    /**
     * Get the fallback value to determine whether the search columns can be toggled.
     *
     * @return bool
     */
    protected function getFallbackCanMatch()
    {
        return (bool) config('refine.matches', false);
    }

    /**
     * Merge a set of searches with the existing searches.
     *
     * @param  array<int, \Honed\Refine\Searches\Search>|\Illuminate\Support\Collection<int, \Honed\Refine\Searches\Search>  $searches
     * @return $this
     */
    public function addSearches($searches)
    {
        if ($searches instanceof Collection) {
            $searches = $searches->all();
        }

        $this->searches = \array_merge($this->searches ?? [], $searches);

        return $this;
    }

    /**
     * Add a single search to the list of searches.
     *
     * @param  \Honed\Refine\Searches\Search  $search
     * @return $this
     */
    public function addSearch($search)
    {
        $this->searches[] = $search;

        return $this;
    }

    /**
     * Retrieve the columns to be used for searching.
     *
     * @return array<int,\Honed\Refine\Searches\Search>
     */
    public function getSearches()
    {
        return once(function () {
            $methodSearches = method_exists($this, 'searches') ? $this->searches() : [];
            $propertySearches = $this->searches ?? [];

            return \array_values(
                \array_filter(
                    \array_merge($propertySearches, $methodSearches),
                    static fn (Search $search) => $search->isAllowed()
                )
            );
        });
    }

    /**
     * Determines if the instance has any searches.
     *
     * @return bool
     */
    public function hasSearch()
    {
        return filled($this->getSearches());
    }

    /**
     * Apply a search to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return $this
     */
    public function search($builder)
    {
        $columns = ($this->canMatch()
            ? $this->getArrayFromQueryParameter($this->getMatchesKey())
            : null) ?? true;

        $term = $this->getSearchTerm();

        $this->term = $term;

        $applied = false;

        foreach ($this->getSearches() as $search) {
            $boolean = $applied ? 'or' : 'and';

            $applied |= $search->apply($builder, $term, $columns, $boolean);
        }

        return $this;
    }

    /**
     * Get the search value from the request.
     *
     * @return string|null
     */
    public function getSearchTerm()
    {
        /** @var string|null */
        $param = $this->getQueryParameter($this->getSearchesKey());

        if (empty($param)) {
            return null;
        }

        return \str_replace('+', ' ', $param);
    }

    /**
     * Retrieve the search value.
     *
     * @return string|null
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Get the searches as an array.
     *
     * @return array<int,array<string,mixed>>
     */
    public function searchesToArray()
    {
        return \array_map(
            static fn (Search $search) => $search->toArray(),
            $this->getSearches()
        );
    }
}
