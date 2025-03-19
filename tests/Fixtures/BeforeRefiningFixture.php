<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Fixtures;

use Honed\Refine\Refine;

class BeforeRefiningFixture extends Refine
{
    public function before($builder)
    {
        return $builder->where('price', '>', 100);
    }
}
