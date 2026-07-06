<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Pipe;
use Honed\Refine\Refine;

/**
 * @extends Pipe<\Honed\Refine\Refine>
 */
class PersistData extends Pipe
{
    /**
     * Run the after refining logic.
     */
    public function run(Refine $instance): void
    {
        foreach ($instance->getDrivers() as $driver) {
            $driver->persist();
        }
    }
}
