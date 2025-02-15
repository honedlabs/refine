<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns\Support;

trait CanMatch
{
    /**
     * Whether the search can select which columns are used to search on.
     *
     * @var bool|null
     */
    protected $matches;

    /**
     * Determine whether the user's preferences should be remembered.
     */
    public function canMatch(): bool
    {
        if (isset($this->matches)) {
            return $this->matches;
        }

        return (bool) config('refine.matches', false);
    }

    /**
     * Set whether the search can select which columns are used to search on.
     *
     * @return $this
     */
    public function matches(bool $matches = true): static
    {
        $this->matches = $matches;

        return $this;
    }
}
