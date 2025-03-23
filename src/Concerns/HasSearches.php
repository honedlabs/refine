<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Search;
use Illuminate\Support\Arr;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 */
trait HasSearches
{
    /**
     * List of the searches.
     *
     * @var array<int,\Honed\Refine\Search<TModel, TBuilder>>|null
     */
    protected $searches;

    /**
     * The query parameter to identify the search string.
     *
     * @var string|null
     */
    protected $searchKey;

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
    protected $matchKey;

    /**
     * The search term as a string without replacements.
     *
     * @var string|null
     */
    protected $term;

    /**
     * Whether to apply the searches.
     *
     * @var bool
     */
    protected $searching = true;

    /**
     * Whether to not provide the searches.
     *
     * @var bool
     */
    protected $withoutSearches = false;

    /**
     * Merge a set of searches with the existing searches.
     *
     * @param  iterable<int, \Honed\Refine\Search<TModel, TBuilder>>  ...$searches
     * @return $this
     */
    public function withSearches(...$searches)
    {
        /** @var array<int, \Honed\Refine\Search<TModel, TBuilder>> $searches */
        $searches = Arr::flatten($searches);

        $this->searches = \array_merge($this->searches ?? [], $searches);

        return $this;
    }

    /**
     * Define the searches for the instance.
     *
     * @return array<int, \Honed\Refine\Search<TModel, TBuilder>>
     */
    public function searches()
    {
        return [];
    }

    /**
     * Retrieve the columns to be used for searching.
     *
     * @return array<int,\Honed\Refine\Search<TModel, TBuilder>>
     */
    public function getSearches()
    {
        if ($this->isWithoutSearches()) {
            return [];
        }

        return once(fn () => \array_values(
            \array_filter(
                \array_merge($this->searches(), $this->searches ?? []),
                static fn (Search $search) => $search->isAllowed()
            )
        ));
    }

    /**
     * Determines if the instance has any searches.
     *
     * @return bool
     */
    public function hasSearches()
    {
        return filled($this->getSearches());
    }

    /**
     * Set the query parameter to identify the search string.
     *
     * @param  string  $searchKey
     * @return $this
     */
    public function searchKey($searchKey)
    {
        $this->searchKey = $searchKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the search.
     *
     * @return string
     */
    public function getSearchKey()
    {
        return $this->searchKey ?? static::getDefaultSearchKey();
    }

    /**
     * Get the default query parameter to identify the search.
     *
     * @return string
     */
    public static function getDefaultSearchKey()
    {
        return type(config('refine.search_key', 'search'))->asString();
    }

    /**
     * Set the query parameter to identify the columns to search.
     *
     * @param  string  $matchKey
     * @return $this
     */
    public function matchKey($matchKey)
    {
        $this->matchKey = $matchKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the columns to search.
     *
     * @return string
     */
    public function getMatchKey()
    {
        return $this->matchKey ?? static::getDefaultMatchKey();
    }

    /**
     * Get the default query parameter to identify the columns to search.
     *
     * @return string
     */
    public static function getDefaultMatchKey()
    {
        return type(config('refine.match_key', 'match'))->asString();
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
     * Determine if matching is enabled
     *
     * @return bool
     */
    public function isMatching()
    {
        return (bool) ($this->match ?? static::isMatchingByDefault());
    }

    /**
     * Determine if matching is enabled from the config.
     *
     * @return bool
     */
    public static function isMatchingByDefault()
    {
        return (bool) config('refine.match', false);
    }

    /**
     * Set the instance to not provide the searches.
     *
     * @param  bool  $withoutSearches
     * @return $this
     */
    public function withoutSearches($withoutSearches = true)
    {
        $this->withoutSearches = $withoutSearches;

        return $this;
    }

    /**
     * Determine if the instance should not provide the searches.
     *
     * @return bool
     */
    public function isWithoutSearches()
    {
        return $this->withoutSearches;
    }

    /**
     * Set the search term.
     *
     * @param  string|null  $term
     * @return $this
     */
    public function term($term)
    {
        $this->term = $term;

        return $this;
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
        if (! $this->isMatching()) {
            return [];
        }

        return \array_map(
            static fn (Search $search) => $search->toArray(),
            $this->getSearches()
        );
    }
}
