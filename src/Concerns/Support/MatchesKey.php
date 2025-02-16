<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns\Support;

trait MatchesKey
{
    /**
     * The query parameter to identify the columns to search on.
     *
     * @var string|null
     */
    protected $matchesKey;

    /**
     * Set the query parameter to identify the columns to search on.
     *
     * @return $this
     */
    public function matchesKey(string $matchesKey): static
    {
        $this->matchesKey = $matchesKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the columns to search on.
     */
    public function getMatchesKey(): string
    {
        if (isset($this->matchesKey)) {
            return $this->matchesKey;
        }

        /** @var string */
        return config('refine.keys.matches', 'match');
    }
}
