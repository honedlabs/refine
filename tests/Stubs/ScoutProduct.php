<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Stubs;

use Laravel\Scout\Searchable;

class ScoutProduct extends Product
{
    use Searchable;

    /**
     * {@inheritdoc}
     */
    protected $table = 'products';
}
