<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Core\Interpreter;
use Honed\Refine\Search;
use Illuminate\Support\Collection;

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
     * Whether to not apply the searches.
     *
     * @var bool
     */
    protected $withoutSearching = false;

    /**
     * Whether to not provide the searches.
     *
     * @var bool
     */
    protected $withoutSearches = false;

    /**
     * Format a value using the scope.
     *
     * @param  string  $value
     * @return string
     */
    abstract public function formatScope($value);

    /**
     * Merge a set of searches with the existing searches.
     *
     * @param  array<int, \Honed\Refine\Search<TModel, TBuilder>>|\Illuminate\Support\Collection<int, \Honed\Refine\Search<TModel, TBuilder>>  $searches
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
     * @param  \Honed\Refine\Search<TModel, TBuilder>  $search
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
     * @return array<int,\Honed\Refine\Search<TModel, TBuilder>>
     */
    public function getSearches()
    {
        return once(function () {

            $searches = \method_exists($this, 'searches') ? $this->searches() : [];

            $searches = \array_merge($searches, $this->searches ?? []);

            return \array_values(
                \array_filter(
                    $searches,
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
     * Get the query parameter to identify the search.
     *
     * @return string
     */
    public function getSearchesKey()
    {
        return $this->searchesKey ?? static::fallbackSearchesKey();
    }

    /**
     * Get the query parameter to identify the search from the config.
     *
     * @return string
     */
    public static function fallbackSearchesKey()
    {
        return type(config('refine.searches_key', 'search'))->asString();
    }

    /**
     * Set the query parameter to identify the columns to search.
     *
     * @param  string  $matchesKey
     * @return $this
     */
    public function matchesKey($matchesKey)
    {
        $this->matchesKey = $matchesKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the columns to search.
     *
     * @return string
     */
    public function getMatchesKey()
    {
        return $this->matchesKey ?? static::fallbackMatchesKey();
    }

    /**
     * Get the query parameter to identify the columns to search from the config.
     *
     * @return string
     */
    public static function fallbackMatchesKey()
    {
        return type(config('refine.matches_key', 'match'))->asString();
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
        return (bool) ($this->match ?? static::fallbackMatching());
    }

    /**
     * Determine if matching is enabled from the config.
     *
     * @return bool
     */
    public static function fallbackMatching()
    {
        return (bool) config('refine.match', false);
    }

    /**
     * Set the instance to not apply the searches.
     *
     * @param  bool  $withoutSearching
     * @return $this
     */
    public function withoutSearching($withoutSearching = true)
    {
        $this->withoutSearching = $withoutSearching;

        return $this;
    }

    /**
     * Determine if the instance should not apply the searches.
     *
     * @return bool
     */
    public function isWithoutSearching()
    {
        return $this->withoutSearching;
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
     * Retrieve the search value.
     *
     * @return string|null
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Get the search term from a request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function getSearchTerm($request)
    {
        $key = $this->formatScope($this->getSearchesKey());

        $term = Interpreter::interpretStringable($request, $key);

        if (\is_null($term) || $term->isEmpty()) {
            return null;
        }

        return $term->replace('+', ' ')->value();
    }

    /**
     * Get the search columns from a request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<int,string>|null
     */
    public function getSearchColumns($request)
    {
        $key = $this->formatScope($this->getMatchesKey());
        $delimiter = $this->getDelimiter();

        /** @var array<int,string>|null */
        return Interpreter::interpretArray($request, $key, $delimiter);
    }

    /**
     * Get the searches as an array.
     *
     * @return array<int,array<string,mixed>>
     */
    public function searchesToArray()
    {
        if ($this->isWithoutSearches() || ! $this->isMatching()) {
            return [];
        }

        return \array_map(
            static fn (Search $search) => $search->toArray(),
            $this->getSearches()
        );
    }

    /**
     * Apply a search to the query.
     *
     * @param  TBuilder  $builder
     * @param  \Illuminate\Http\Request  $request
     * @param  array<int, \Honed\Refine\Search<TModel, TBuilder>>  $searches
     * @return $this
     */
    public function search($builder, $request, $searches = [])
    {
        if ($this->isWithoutSearching()) {
            return $this;
        }

        $term = $this->getSearchTerm($request);
        $columns = $this->getSearchColumns($request);

        $this->term = $term;

        /** @var array<int, \Honed\Refine\Search<TModel, TBuilder>> */
        $searches = \array_merge($this->getSearches(), $searches);

        $applied = false;

        foreach ($searches as $search) {
            $boolean = $applied ? 'or' : 'and';

            $matched = empty($columns) ||
                \in_array($search->getParameter(), $columns);

            if ($matched) {
                $applied |= $search->boolean($boolean)->refine($builder, $term);
            }

        }

        return $this;
    }
}
