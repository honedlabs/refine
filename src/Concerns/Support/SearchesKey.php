<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns\Support;

trait SearchesKey
{
    /**
     * The query parameter to identify the search string.
     * 
     * @var string|null
     */
    protected $searchesKey;

    /**
     * Set the query parameter to identify the search string.
     * 
     * @return $this
     */
    public function searchesKey(string $searchesKey): static
    {
        $this->searchesKey = $searchesKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the search string.
     */
    public function getSearchesKey(): string
    {
        if (isset($this->searchesKey)) {
            return $this->searchesKey;
        }

        /** @var string */
        return config('refine.keys.searches', 'search');
    }
}
