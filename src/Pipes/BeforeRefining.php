<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Pipe;

/**
 * @template TClass of \Honed\Refine\Contracts\RefinesData
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
