<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Concerns\Support\CanMatch;
use Illuminate\Http\Request;
use Honed\Refine\Searches\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Arrayable;
use Honed\Refine\Concerns\Support\MatchesKey;
use Honed\Refine\Concerns\Support\SearchesKey;

trait HasSearches
{
    use MatchesKey;
    use SearchesKey;
    use CanMatch;

    /**
     * The search value as a string without replacements.
     *
     * @var string|null
     */
    protected $searchValue;

    /**
     * List of the searches.
     *
     * @var array<int,\Honed\Refine\Searches\Search>|null
     */
    protected $searches;

    /**
     * Merge a set of searches with the existing searches.
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
         * @var array<int, \Honed\Refine\Searches\Search> $searches
         */
        $this->searches = \array_merge($this->searches ?? [], $searches);

        return $this;
    }

    /**
     * Add a single search to the list of searches.
     * 
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
        return $this->searches ??= $this->getSourceSearches();
    }

    /**
     * Retrieve the searches which are available.
     * 
     * @return array<int,\Honed\Refine\Searches\Search>
     */
    protected function getSourceSearches(): array
    {
        $searches = match (true) {
            \method_exists($this, 'searches') => $this->searches(),
            default => [],
        };

        return \array_filter(
            $searches,
            fn (Search $search) => $search->isAllowed()
        );
    }

    /**
     * Determines if the instance has any searches.
     */
    public function hasSearch(): bool
    {
        return filled($this->getSearches());
    }

    /**
     * Apply a search to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return $this
     */
    public function search(Builder $builder, Request $request): static
    {
        $columns = $this->canMatch()
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
        $matches = $request->string($this->getMatchesKey(), null);

        if ($matches->isEmpty()) {
            return true;
        }

        /** @var array<int,string> */
        return $matches
            ->explode(',')
            ->map(fn ($value) => \trim($value))
            ->toArray();
    }

    /**
     * Get the search value from the request.
     */
    public function getSearchFromRequest(Request $request): ?string
    {
        $search = $request->string($this->getSearchesKey(), null);

        if ($search->isEmpty()) {
            return null;
        }

        return $search
            ->replace('+', ' ')
            ->toString();
    }

    /**
     * Retrieve the search value.
     */
    public function getSearchValue(): ?string
    {
        return $this->searchValue;
    }

    /**
     * Get the searches as an array.
     * 
     * @return array<int,mixed>
     */
    public function searchesToArray(): array
    {
        return \array_map(
            static fn (Search $search) => $search->toArray(), 
            $this->getSearches()
        );
    }
}
