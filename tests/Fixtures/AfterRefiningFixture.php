<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Fixtures;

use Honed\Refine\Contracts\RefinesAfter;
use Honed\Refine\Refine;

class AfterRefiningFixture extends Refine implements RefinesAfter
{
    /**
     * Define a callback to be applied after the refiners have been applied.
     *
     * @param  TBuilder  $builder
     * @return void
     */
    public function afterRefining($builder)
    {
        return $builder->where('price', '>', 100);
    }
}
