<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Core\Exceptions\InvalidResourceException;
use Honed\Core\Exceptions\ResourceNotSetException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use function is_string;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 */
trait HasResource
{
    /**
     * The builder instance.
     *
     * @var TBuilder|null
     */
    protected $resource;

    /**
     * Set the resource to be used.
     *
     * @param  TBuilder|TModel|class-string<TModel>  $resource
     * @return $this
     *
     * @throws InvalidResourceException
     */
    public function resource($resource)
    {
        $this->resource = $this->throughBuilder($resource);

        return $this;
    }

    /**
     * Set the resource to be used.
     *
     * @param  TBuilder|TModel|class-string<TModel>  $resource
     * @return $this
     */
    public function for($resource)
    {
        return $this->resource($resource);
    }

    /**
     * Get a builder instance of the resource.
     *
     * @return TBuilder
     *
     * @throws ResourceNotSetException
     */
    public function getBuilder()
    {
        if (! $this->resource) {
            throw ResourceNotSetException::throw(static::class);
        }

        return $this->resource;
    }

    /**
     * Get a model instance.
     *
     * @return TModel
     *
     * @throws ResourceNotSetException
     */
    public function getModel()
    {
        return $this->getBuilder()->getModel();
    }

    /**
     * Create a new builder instance from a resource.
     *
     * @param  TBuilder|TModel|class-string<TModel>|null  $resource
     * @return TBuilder|null
     *
     * @throws InvalidResourceException
     */
    protected function throughBuilder($resource)
    {
        return match (true) {
            $resource instanceof Builder => $resource,
            $resource instanceof Model => $resource::query(),
            is_string($resource) => $resource::query(),
            default => InvalidResourceException::throw(static::class),
        };
    }
}
