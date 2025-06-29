<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

interface CanPersistData
{
    /**
     * Get the name of the key to use when persisting data.
     *
     * @return string
     */
    public function getPersistKey();

    /**
     * Get the store to use for persisting data.
     *
     * @param  bool|string|null  $type
     * @return Store|null
     */
    public function getStore($type = null);

    /**
     * Get the stores to use for persisting data.
     *
     * @return array<string,\Honed\Refine\Stores\Store>
     */
    public function getStores();

    // persist[name]

    // persist[name]In[driver]

    // shouldPersist[name]

    // get[name]Store
}