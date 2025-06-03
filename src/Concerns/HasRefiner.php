<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Attributes\Refiner;
use Honed\Refine\Refine;
use ReflectionClass;

/**
 * @template TRefine of \Honed\Refine\Refine
 *
 * @property-read string|null $refiner The class string of the refine for this model.
 */
trait HasRefiner
{
    /**
     * Get the refine instance for the model.
     *
     * @return TRefine
     */
    public static function refiner()
    {
        return static::newRefiner()
            ?? Refine::refinerForModel(static::class);
    }

    /**
     * Create a new refine instance for the model.
     *
     * @return TRefine|null
     */
    protected static function newRefiner()
    {
        if (isset(static::$refiner)) {
            return static::$refiner::make();
        }

        if ($refiner = static::getRefinerAttribute()) {
            return $refiner::make();
        }

        return null;
    }

    /**
     * Get the refine from the Refine class attribute.
     *
     * @return class-string<Refine>|null
     */
    protected static function getRefinerAttribute()
    {
        $attributes = (new ReflectionClass(static::class))
            ->getAttributes(Refiner::class);

        if ($attributes !== []) {
            $refiner = $attributes[0]->newInstance();

            return $refiner->getRefiner();
        }

        return null;
    }
}
