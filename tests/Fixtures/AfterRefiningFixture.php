<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Fixtures;

use Honed\Refine\Refine;

class AfterRefiningFixture extends Refine
{
    public function after($builder)
    {
        return $builder->where('price', '>', 100);
    }
}
