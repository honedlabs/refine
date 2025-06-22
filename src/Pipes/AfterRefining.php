<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Pipe;

/**
 * @template TClass of \Honed\Refine\Contracts\RefinesData
 *
 * @extends Pipe<TClass>
 */
class AfterRefining extends Pipe
{
    /**
     * Run the after refining logic.
     *
     * @param  TClass  $instance
     * @return void
     */
    public function run($instance)
    {
        $after = $instance->getAfterCallback();

        $instance->evaluate($after);
    }
}
