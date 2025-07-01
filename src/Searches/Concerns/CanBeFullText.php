<?php

declare(strict_types=1);

namespace Honed\Refine\Searches\Concerns;

trait CanBeFullText
{
    /**
     * Whether to use a full-text, recall search.
     */
    protected bool $fullText = false;

    /**
     * Set whether to use a full-text search.
     *
     * @return $this
     */
    public function fullText(bool $value = true): static
    {
        $this->fullText = $value;

        return $this;
    }

    /**
     * Set whether to not use a full-text search.
     *
     * @return $this
     */
    public function notFullText(bool $value = true): static
    {
        return $this->fullText(! $value);
    }

    /**
     * Determine if the search is a full-text search.
     */
    public function isFullText(): bool
    {
        return $this->fullText;
    }

    /**
     * Determine if the search is not a full-text search.
     */
    public function isNotFullText(): bool
    {
        return ! $this->fullText;
    }
}
