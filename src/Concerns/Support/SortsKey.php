<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns\Support;

trait SortsKey
{
    /**
     * The query parameter to identify the sort to apply.
     * 
     * @var string|null
     */
    protected $sortsKey;

    /**
     * Set the query parameter to identify the sort to apply.
     * 
     * @return $this
     */
    public function sortsKey(string $sortsKey): static
    {
        $this->sortsKey = $sortsKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the sort to apply.
     */
    public function getSortsKey(): string
    {
        if (isset($this->sortsKey)) {
            return $this->sortsKey;
        }

        /** @var string */
        return config('refine.keys.sorts', 'sort');
    }
}
