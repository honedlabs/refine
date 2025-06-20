<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

/**
 * @template TClass of \Honed\Refine\Refine
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
