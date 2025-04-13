<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Concerns\Allowable;
use Honed\Core\Concerns\HasAlias;
use Honed\Core\Concerns\HasLabel;
use Honed\Core\Concerns\HasMeta;
use Honed\Core\Concerns\HasName;
use Honed\Core\Concerns\HasQuery;
use Honed\Core\Concerns\HasType;
use Honed\Core\Concerns\HasValue;
use Honed\Core\Primitive;
use Honed\Refine\Concerns\HasQualifier;
use Illuminate\Support\Str;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @method void defaultQuery(TBuilder $builder, mixed ...$parameters) Apply the default refiner query to the builder.
 */
abstract class Refiner extends Primitive
{
    use Allowable;
    use HasAlias;
    use HasLabel;
    use HasMeta;
    use HasName;
    use HasQualifier;

    /** @use HasQuery<TModel, TBuilder> */
    use HasQuery;

    use HasType;
    use HasValue;

    /**
     * Create a new refiner instance.
     *
     * @param  string  $name
     * @param  string|null  $label
     * @return static
     */
    public static function make($name, $label = null)
    {
        return resolve(static::class)
            ->name($name)
            ->label($label ?? static::makeLabel($name));
    }

    /**
     * Determine if the refiner is currently being applied.
     *
     * @return bool
     */
    public function isActive()
    {
        return filled($this->getValue());
    }

    /**
     * Get the value for the refiner from the request.
     *
     * @param  \Illuminate\Http\Request|mixed  $value
     * @return mixed
     */
    public function getRequestValue($value)
    {
        return $value;
    }

    /**
     * Get the parameter for the refiner.
     *
     * @return string
     */
    public function getParameter()
    {
        return $this->getAlias() ?? $this->guessParameter();
    }

    /**
     * Guess the parameter for the refiner.
     *
     * @return string
     */
    public function guessParameter()
    {
        return Str::of($this->getName())
            ->afterLast('.')
            ->value();
    }

    /**
     * Transform the value for the refiner from the request.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function transformParameter($value)
    {
        return $value;
    }

    /**
     * Determine if the value is invalid.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function invalidValue($value)
    {
        return false;
    }

    /**
     * Get the bindings for the refiner closure.
     *
     * @param  mixed  $value
     * @param  TBuilder  $builder
     * @return array<string,mixed>
     */
    public function getBindings($value, $builder)
    {
        return [
            'value' => $value,
            'column' => $this->qualifyColumn($this->getName(), $builder),
        ];
    }

    /**
     * Refine the builder using the request.
     *
     * @param  TBuilder  $builder
     * @param  \Illuminate\Http\Request|mixed  $requestValue
     * @return bool
     */
    public function refine($builder, $requestValue)
    {
        $value = $this->getRequestValue($requestValue);

        $value = $this->transformParameter($value);

        $this->value($value);

        if (! $this->isActive() || $this->invalidValue($value)) {
            return false;
        }

        $bindings = $this->getBindings($value, $builder);

        if (! $this->hasQuery()) {
            $this->query(\Closure::fromCallable([$this, 'defaultQuery']));
        }

        $this->modifyQuery($builder, $bindings);

        return true;
    }

    /**
     * Get the refiner as an array.
     *
     * @return array<string,mixed>
     */
    public function toArray()
    {
        return [
            'name' => $this->getParameter(),
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'active' => $this->isActive(),
            'meta' => $this->getMeta(),
        ];
    }
}
