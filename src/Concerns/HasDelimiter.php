<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

trait HasDelimiter
{
    public const DELIMITER = ',';

    /**
     * The delimiter to use for parsing array values.
     */
    protected string $delimiter = self::DELIMITER;

    /**
     * Set the delimiter.
     *
     * @return $this
     */
    public function delimiter(string $delimiter): static
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Get the delimiter.
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }
}
