<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Searches\Search;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasSearch
{
    const SearchKey = 'search';

    const MatchKey = 'match';

    /**
     * Whether the search can select which columns are used to search on.
     *
     * @var bool
     */
    protected $matches = false;

    /**
     * The search value as a string without replacements.
     *
     * @var string|null
     */
    protected $searchValue;

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
    protected $matchKey = self::MatchKey;

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
        return \count($this->getSearches()) > 0;
    }

    /**
     * Search the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return $this
     */
    public function search(Builder $builder, Request $request): static
    {
        $columns = $this->hasMatches()
            ? $this->getMatchesFromRequest($request)
            : true;

        $searchFor = $this->getSearchFromRequest($request);

        $this->searchValue = $searchFor;

        $applied = false;

        foreach ($this->getSearches() as $search) {
            $applied |= $search->apply($builder, $searchFor, $columns, $applied ? 'or' : 'and');
        }

        return $this;
    }

    /**
     * Retrieve the columns to be used for searching from the request.
     *
     * @return array<int,string>|true
     */
    public function getMatchesFromRequest(Request $request): array|true
    {
        $matches = $request->string($this->getMatchKey(), null);

        if ($matches->isEmpty()) {
            return true;
        }

        /** @var array<int,string> */
        return $matches
            ->explode(',', PHP_INT_MAX)
            ->map(fn ($v) => \trim($v))
            ->toArray();
    }

    /**
     * Get the search value from the request.
     */
    public function getSearchFromRequest(Request $request): ?string
    {
        $search = $request->string($this->getSearchKey(), null);

        if ($search->isEmpty()) {
            return null;
        }

        return $search
            ->replace('+', ' ')
            ->toString();
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
     * Sets the match key to look for in the request.
     *
     * @return $this
     */
    public function matchKey(string $matchKey): static
    {
        $this->matchKey = $matchKey;

        return $this;
    }

    /**
     * Gets the match key to look for in the request.
     */
    public function getMatchKey(): string
    {
        return $this->matchKey;
    }

    /**
     * Sets the search to be able to select which columns are used to search on.
     *
     * @return $this
     */
    public function matches(): static
    {
        $this->matches = true;

        return $this;
    }

    /**
     * Determine whether the search can select which columns are used to search on.
     */
    public function hasMatches(): bool
    {
        return $this->matches;
    }

    /**
     * Retrieve the search value.
     */
    public function getSearchValue(): ?string
    {
        return $this->searchValue;
    }
}
