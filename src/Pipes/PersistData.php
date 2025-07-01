<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Pipe;

/**
 * @template TClass of \Honed\Refine\Refine
 *
 * @extends Pipe<TClass>
 */
class PersistData extends Pipe
{
    /**
     * Run the after refining logic.
     */
    public function run(): void
    {
        foreach ($this->instance->getDrivers() as $driver) {
            $driver->persist();
        }
    }
}
