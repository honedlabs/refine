<?php

declare(strict_types=1);

namespace Honed\Refine;

use Closure;
use Honed\Core\Concerns\Allowable;
use Honed\Core\Concerns\CanBeActive;
use Honed\Core\Concerns\HasAlias;
use Honed\Core\Concerns\HasLabel;
use Honed\Core\Concerns\HasMeta;
use Honed\Core\Concerns\HasName;
use Honed\Core\Concerns\HasQuery;
use Honed\Core\Primitive;
use Honed\Refine\Concerns\CanBeHidden;
use Honed\Refine\Concerns\HasQualifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @method void apply(TBuilder $builder, mixed ...$parameters) Apply the refiner query to apply to the builder.
 */
abstract class Refiner extends Primitive
{
    use Allowable;
    use CanBeActive;
    use CanBeHidden;
    use HasAlias;

    use HasLabel;

    use HasMeta;
    use HasName;
    use HasQualifier;
    /** @use HasQuery<TModel, TBuilder> */
    use HasQuery;

    /**
     * Provide the instance with any necessary setup.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->define();
    }

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
     * Get the parameter for the refiner.
     */
    public function getParameter(): string
    {
        return $this->getAlias() ?? $this->guessParameter();
    }

    /**
     * Get the qualified attribute name.
     *
     * @param  TBuilder  $query
     */
    public function getQualifiedName(Builder $query): string
    {
        return $this->qualifyColumn($this->getName(), $query);
    }

    /**
     * Get the representation of the instance.
     *
     * @return array<string, mixed>
     */
    protected function representation(): array
    {
        return [
            'name' => $this->getParameter(),
            'label' => $this->getLabel(),
            'active' => $this->isActive(),
            'meta' => $this->getMeta(),
        ];
    }

    /**
     * Handle refining the query.
     *
     * @param  TBuilder  $query
     * @param  array<string,mixed>  $bindings
     * @return true
     */
    protected function refine($query, $bindings)
    {
        if (! $this->queryCallback()) {
            $this->query(Closure::fromCallable([$this, 'apply']));
        }

        $this->callQuery($bindings);

        return true;
    }

    /**
     * Guess the parameter for the refiner.
     *
     * @return string
     */
    protected function guessParameter()
    {
        return Str::afterLast($this->getName(), '.');
    }

    /**
     * Get the bindings for the refiner closure.
     *
     * @param  TBuilder  $query
     * @return array<string,mixed>
     */
    protected function getBindings($query)
    {
        /** @var string */
        $name = $this->getName();

        return [
            'builder' => $query,
            'query' => $query,
            'q' => $query,
            'name' => $name,
            'refiner' => $this,
            'column' => $this->qualifyColumn($name, $query),
        ];
    }
}
