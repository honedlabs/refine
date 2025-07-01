<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

trait CanBeHidden
{
    /**
     * Whether the instance should be serialized.
     */
    protected bool $hidden = false;

    /**
     * Set whether the instance should be hidden from serialization
     *
     * @return $this
     */
    public function hidden(bool $value = true): static
    {
        $this->hidden = $value;

        return $this;
    }

    /**
     * Set whether the instance should not be hidden from serialization.
     *
     * @return $this
     */
    public function notHidden(bool $value = true): static
    {
        return $this->hidden(! $value);
    }

    /**
     * Get whether the instance is hidden from serialization.
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Get whether the instance is not hidden from serialization.
     */
    public function isNotHidden(): bool
    {
        return ! $this->isHidden();
    }
}
