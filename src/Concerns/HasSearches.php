<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Search;
use Illuminate\Support\Arr;

use function array_filter;
use function array_map;
use function array_merge;
use function array_values;

trait HasSearches
{
    /**
     * Whether the searches should be applied.
     *
     * @var bool
     */
    protected $search = true;

    /**
     * List of the searches.
     *
     * @var array<int,Search>
     */
    protected $searches = [];

    /**
     * The query parameter to identify the search string.
     *
     * @var string|null
     */
    protected $searchKey;

    /**
     * The default query parameter to identify the search string.
     *
     * @var string
     */
    protected static $useSearchKey = 'search';

    /**
     * Whether the search columns can be toggled.
     *
     * @var bool|null
     */
    protected $match;

    /**
     * Whether the search columns can be toggled by default.
     *
     * @var bool|null
     */
    protected static $shouldMatch = false;

    /**
     * The query parameter to identify the columns to search on.
     *
     * @var string|null
     */
    protected $matchKey;

    /**
     * The default query parameter to identify the columns to search on.
     *
     * @var string
     */
    protected static $useMatchKey = 'match';

    /**
     * The search term as a string without replacements.
     *
     * @var string|null
     */
    protected $term;

    /**
     * Set the default query parameter to identify the search.
     *
     * @param  string  $searchKey
     * @return void
     */
    public static function useSearchKey($searchKey = 'search')
    {
        static::$useSearchKey = $searchKey;
    }

    /**
     * Set the default query parameter to identify the columns to search.
     *
     * @param  string  $matchKey
     * @return void
     */
    public static function useMatchKey($matchKey = 'match')
    {
        static::$useMatchKey = $matchKey;
    }

    /**
     * Determine if matching is enabled from the config.
     *
     * @param  bool  $match
     * @return void
     */
    public static function shouldMatch($match = true)
    {
        static::$shouldMatch = $match;
    }

    /**
     * Set whether the searchs should be applied.
     *
     * @param  bool  $search
     * @return $this
     */
    public function search($search = true)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Set the searchs to not be applied.
     *
     * @return $this
     */
    public function doNotSearch()
    {
        return $this->search(false);
    }

    /**
     * Set the searchs to not be applied.
     *
     * @return $this
     */
    public function dontSearch()
    {
        return $this->doNotSearch();
    }

    /**
     * Determine if the searchs should be applied.
     *
     * @return bool
     */
    public function shouldSearch()
    {
        return $this->search;
    }

    /**
     * Determine if the searchs should not be applied.
     *
     * @return bool
     */
    public function shouldNotSearch()
    {
        return ! $this->shouldSearch();
    }

    /**
     * Determine if the searchs should not be applied.
     *
     * @return bool
     */
    public function shouldntSearch()
    {
        return $this->shouldNotSearch();
    }

    /**
     * Define the searches for the instance.
     *
     * @return array<int, Search>
     */
    public function searches()
    {
        return [];
    }

    /**
     * Merge a set of searches with the existing searches.
     *
     * @param  Search|iterable<int, Search>  ...$searches
     * @return $this
     */
    public function withSearches(...$searches)
    {
        /** @var array<int, Search> $searches */
        $searches = Arr::flatten($searches);

        $this->searches = array_merge($this->searches, $searches);

        return $this;
    }

    /**
     * Retrieve the columns to be used for searching.
     *
     * @return array<int,Search>
     */
    public function getSearches()
    {
        if ($this->shouldNotSearch()) {
            return [];
        }

        return once(fn () => array_values(
            array_filter(
                array_merge($this->searches(), $this->searches),
                static fn (Search $search) => $search->isAllowed()
            )
        ));
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
        return $this->searchKey ?? static::$useSearchKey;
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
        return $this->matchKey ?? static::$useMatchKey;
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
    public function matches()
    {
        return (bool) ($this->match ?? static::$shouldMatch);
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
     * Determine if there is a search being applied.
     *
     * @return bool
     */
    public function isSearching()
    {
        return filled($this->getTerm());
    }

    /**
     * Get the searches as an array.
     *
     * @return array<int,array<string,mixed>>
     */
    public function searchesToArray()
    {
        if (! $this->matches()) {
            return [];
        }

        return array_map(
            static fn (Search $search) => $search->toArray(),
            $this->getSearches()
        );
    }
}
