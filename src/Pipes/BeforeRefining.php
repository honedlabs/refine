<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

/**
 * @template TClass of \Honed\Refine\Refine
 *
 * @extends Pipe<TClass>
 */
class BeforeRefining extends Pipe
{
    /**
     * Run the before refining logic.
     *
     * @param  TClass  $instance
     * @return void
     */
    public function run($instance)
    {
        $before = $instance->getBeforeCallback();

        $instance->evaluate($before);
    }
}
