<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Fixtures;

use Honed\Refine\Contracts\RefinesBefore;
use Honed\Refine\Refine;

class BeforeRefiningFixture extends Refine implements RefinesBefore
{
    /**
     * Define a callback to be applied before the refiners have been applied.
     *
     * @param  TBuilder  $builder
     * @return void
     */
    public function beforeRefining($builder)
    {
        return $builder->where('price', '>', 100);
    }
}
