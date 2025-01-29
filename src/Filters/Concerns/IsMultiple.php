<?php

declare(strict_types=1);

namespace Honed\Refine\Filters\Concerns;

trait IsMultiple
{
    protected bool $multiple = false;

    /**
     * @return $this
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }
}
