<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

trait CanBeNullable
{
    /**
     * Whether the instance is nullable.
     *
     * @var bool
     */
    protected $nullable = false;

    /**
     * Set whether the instance is nullable.
     *
     * @return $this
     */
    public function nullable(bool $value = true): static
    {
        $this->nullable = $value;

        return $this;
    }

    /**
     * Set whether the instance is not nullable.
     *
     * @return $this
     */
    public function notNullable(bool $value = true): static
    {
        return $this->nullable(! $value);
    }

    /**
     * Determine if the instance is nullable.
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Determine if the instance is not nullable.
     */
    public function isNotNullable(): bool
    {
        return ! $this->isNullable();
    }
}
