<?php

declare(strict_types=1);

namespace Honed\Refine\Filters\Concerns;

trait IsMultiple
{
    /**
     * @var bool
     */
    protected $multiple = false;

    /**
     * Set the filter to allow multiple values.
     * 
     * @return $this
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Determine if the filter allows multiple values.
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }
}
