<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

interface SortsData
{
    /**
     * Retrieve the sorts.
     *
     * @return array<int,\Honed\Refine\Sorts\Sort>
     */
    public function getSorts();

    /**
     * Get the query parameter to identify the sort to apply.
     *
     * @return string
     */
    public function getSortKey();

    /**
     * Get the default sort.
     *
     * @return \Honed\Refine\Sorts\Sort|null
     */
    public function getDefaultSort();

    /**
     * Get the store to use for persisting sorts.
     *
     * @return \Honed\Refine\Stores\Store|null
     */
    public function getSortStore();

    /**
     * Determine if the sort should be persisted.
     *
     * @return bool
     */
    public function shouldPersistSort();
}
