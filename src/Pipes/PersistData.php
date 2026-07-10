<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Pipe;
use Honed\Refine\Contracts\Refine;

/**
 * @extends Pipe<\Honed\Refine\Contracts\Refine&\Honed\Core\Primitive>
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
