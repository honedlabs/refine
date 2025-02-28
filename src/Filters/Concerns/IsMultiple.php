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
     * @param  bool  $multiple
     * @return $this
     */
    public function multiple($multiple = true)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Determine if the filter allows multiple values.
     *
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }
}
