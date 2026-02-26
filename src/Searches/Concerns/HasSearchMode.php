<?php

declare(strict_types=1);

namespace Honed\Refine\Searches\Concerns;

use Honed\Refine\Enums\SearchMode;

trait HasSearchMode
{
    /**
     * The search mode to use.
     */
    protected SearchMode $searchMode = SearchMode::Wildcard;

    /**
     * Set the search mode to use.
     *
     * @return $this
     */
    public function searchMode(SearchMode $value): static
    {
        $this->searchMode = $value;

        return $this;
    }

    /**
     * Set the search mode to be a wildcard search.
     *
     * @return $this
     */
    public function wildcard(): static
    {
        return $this->searchMode(SearchMode::Wildcard);
    }

    /**
     * Set the search mode to be a starts-with search.
     *
     * @return $this
     */
    public function startsWith(): static
    {
        return $this->searchMode(SearchMode::StartsWith);
    }

    /**
     * Set the search mode to be an ends-with search.
     *
     * @return $this
     */
    public function endsWith(): static
    {
        return $this->searchMode(SearchMode::EndsWith);
    }

    /**
     * Set the search mode to be a full-text search, as natural language.
     *
     * @return $this
     */
    public function fullText(SearchMode $value = SearchMode::NaturalLanguage): static
    {
        return $this->searchMode($value);
    }

    /**
     * Set the search mode to be a natural language search.
     *
     * @return $this
     */
    public function naturalLanguage(): static
    {
        return $this->searchMode(SearchMode::NaturalLanguage);
    }

    /**
     * Set the search mode to be a boolean search.
     *
     * @return $this
     */
    public function boolean(): static
    {
        return $this->searchMode(SearchMode::Boolean);
    }

    /**
     * Get the search mode to use.
     */
    public function getSearchMode(): SearchMode
    {
        return $this->searchMode;
    }
}
