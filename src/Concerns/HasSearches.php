<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Search;
use Illuminate\Support\Arr;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @phpstan-require-extends \Honed\Core\Primitive
 */
trait HasSearches
{
    /**
     * List of the searches.
     *
     * @var array<int,\Honed\Refine\Search<TModel, TBuilder>>
     */
    protected $searches = [];

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
     * Merge a set of searches with the existing searches.
     *
     * @param  \Honed\Refine\Search<TModel, TBuilder>|iterable<int, \Honed\Refine\Search<TModel, TBuilder>>  ...$searches
     * @return $this
     */
    public function searches(...$searches)
    {
        /** @var array<int, \Honed\Refine\Search<TModel, TBuilder>> $searches */
        $searches = Arr::flatten($searches);

        $this->searches = \array_merge($this->searches, $searches);

        return $this;
    }

    /**
     * Define the searches for the instance.
     *
     * @return array<int, \Honed\Refine\Search<TModel, TBuilder>>
     */
    public function defineSearches()
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
        if (! $this->providesSearches()) {
            return [];
        }

        return once(fn () => \array_values(
            \array_filter(
                \array_merge($this->defineSearches(), $this->searches),
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
     * Determine if there is a search being applied.
     *
     * @return bool
     */
    public function isSearching()
    {
        return filled($this->getTerm());
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
     * @param  bool|null  $matches
     * @return $this
     */
    public function matches($matches = true)
    {
        $this->match = $matches;

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
     * @return $this
     */
    public function exceptSearches()
    {
        return $this->except('searches');
    }

    /**
     * Set the instance to provide only searches.
     *
     * @return $this
     */
    public function onlySearches()
    {
        return $this->only('searches');
    }

    /**
     * Determine if the instance provides the searches.
     *
     * @return bool
     */
    public function providesSearches()
    {
        return $this->has('searches');
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
