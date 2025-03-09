<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Search;
use Illuminate\Support\Collection;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
trait HasSearches
{
    /**
     * List of the searches.
     *
     * @var array<int,\Honed\Refine\Search>|null
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
     * Merge a set of searches with the existing searches.
     *
     * @param  array<int, \Honed\Refine\Search>|\Illuminate\Support\Collection<int, \Honed\Refine\Search>  $searches
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
     * @param  \Honed\Refine\Search  $search
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
     * @return array<int,\Honed\Refine\Search>
     */
    public function getSearches()
    {
        return once(function () {
            $searches = \method_exists($this, 'searches') ? $this->searches() : [];

            $searches = \array_merge($searches, $this->searches ?? []);

            return collect($searches)
                ->filter(static fn (Search $search) => $search->isAllowed())
                // ->unique(static fn (Search $search) => $search->getUniqueKey())
                ->values()
                ->all();
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
     * Determine if the searches key is set.
     * 
     * @return bool
     */
    public function hasSearchesKey()
    {
        return isset($this->searchesKey);
    }

    /**
     * Get the query parameter to identify the search string.
     *
     * @return string
     */
    public function getSearchesKey()
    {
        if ($this->hasSearchesKey()) {
            return type($this->searchesKey)->asString();
        }

        return $this->fallbackSearchesKey();
    }

    /**
     * Get the query parameter to identify the search string from the config.
     *
     * @return string
     */
    protected function fallbackSearchesKey()
    {
        return type(config('refine.config.searches', 'search'))->asString();
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
     * Determine if the matches key is set.
     * 
     * @return bool
     */
    public function hasMatchesKey()
    {
        return isset($this->matchesKey);
    }

    /**
     * Get the query parameter to identify the columns to search.
     *
     * @return string
     */
    public function getMatchesKey()
    {
        if ($this->hasMatchesKey()) {
            return type($this->matchesKey)->asString();
        }

        return $this->fallbackMatchesKey();
    }

    /**
     * Get the query parameter to identify the columns to search from the config.
     *
     * @return string
     */
    protected function fallbackMatchesKey()
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
     * Determine if the matching value is set.
     * 
     * @return bool
     */
    public function hasMatch()
    {
        return isset($this->match);
    }

    /**
     * Determine if matching is enabled
     * 
     * @return bool
     */
    public function isMatching()
    {
        if ($this->hasMatch()) {
            return (bool) $this->match;
        }

        return $this->fallbackIsMatching();
    }

    /**
     * Determine if matching is enabled from the config.
     *
     * @return bool
     */
    protected function fallbackIsMatching()
    {
        return (bool) config('refine.matches', false);
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
     * Get the search columns from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<int,string>|null
     */
    public function getMatches($request)
    {
        if (! $this->isMatching()) {
            return null;
        }

        /** @var string */
        $key = $this->formatScope($this->getMatchesKey());

        $columns = $request->safeArray($key, null, $this->getDelimiter());

        if (\is_null($columns) || $columns->isEmpty()) {
            return null;
        }

        return $columns
            ->map(\trim(...))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Get the search value from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function getSearch($request)
    {
        /** @var string */
        $key = $this->formatScope($this->getSearchesKey());

        $param = $request->safeString($key);

        if ($param->isEmpty()) {
            return null;
        }

        return $param->replace('+', ' ')->value();
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
     * Apply a search to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>  $builder
     * @param  \Illuminate\Http\Request  $request
     * @param  array<int, \Honed\Refine\Search>  $searches
     * @return $this
     */
    public function search($builder, $request, $searches = [])
    {
        if ($this->isWithoutSearching()) {
            return $this;
        }

        $columns = $this->getMatches($request);
        $this->term = $this->getSearch($request);

        /** @var array<int, \Honed\Refine\Search> */
        $searches = \array_merge($this->getSearches(), $searches);
        $applied = false;

        foreach ($searches as $search) {
            $boolean = $applied ? 'or' : 'and';

            $applied |= $search->refine($builder, $this->term, $columns, $boolean);
        }

        return $this;
    }

    /**
     * Get the searches as an array.
     *
     * @return array<int,array<string,mixed>>
     */
    public function searchesToArray()
    {
        if ($this->isWithoutSearches()) {
            return [];
        }

        return \array_map(
            static fn (Search $search) => $search->toArray(),
            $this->getSearches()
        );
    }
}
