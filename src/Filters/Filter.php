<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use BackedEnum;
use Carbon\CarbonInterface;
use Honed\Core\Concerns\CanHaveDefault;
use Honed\Core\Concerns\HasType;
use Honed\Core\Concerns\HasValue;
use Honed\Core\Concerns\InterpretsRequest;
use Honed\Core\Concerns\Validatable;
use Honed\Refine\Filters\Concerns\HasOperator;
use Honed\Refine\Filters\Concerns\HasOptions;
use Honed\Refine\Refiner;
use Honed\Refine\Searches\Search;
use ReflectionEnum;

use function array_merge;
use function in_array;
use function is_string;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Refiner<TModel, TBuilder>
 */
class Filter extends Refiner
{
    use CanHaveDefault;
    use HasOperator;
    use HasOptions {
        multiple as protected setMultiple;
    }
    use HasType;
    use HasValue;
    use InterpretsRequest;
    use Validatable;

    public const BOOLEAN = 'boolean';

    public const DATE = 'date';

    public const DATETIME = 'datetime';

    public const NUMBER = 'number';

    public const SELECT = 'select';

    public const TEXT = 'text';

    public const TIME = 'time';

    public const TRASHED = 'trashed';

    /**
     * The identifier to use for evaluation.
     *
     * @var string
     */
    protected $evaluationIdentifier = 'filter';

    /**
     * Whether the filter only responds to presence values.
     */
    protected bool $presence = false;

    /**
     * Set the filter to be for boolean values.
     *
     * @return $this
     */
    public function boolean(): static
    {
        $this->type(self::BOOLEAN);
        $this->asBoolean();

        return $this;
    }

    /**
     * Set the filter to be for date values.
     *
     * @return $this
     */
    public function date(): static
    {
        $this->type(self::DATE);
        $this->asDate();

        return $this;
    }

    /**
     * Set the filter to be for date time values.
     *
     * @return $this
     */
    public function datetime(): static
    {
        $this->type(self::DATETIME);
        $this->asDatetime();

        return $this;
    }

    /**
     * Set the filter to use options from an enum.
     *
     * @param  class-string<BackedEnum>  $enum
     * @param  bool  $multiple
     * @return $this
     */
    public function enum($enum, $multiple = false)
    {
        $this->options($enum);

        /** @var 'int'|'string'|null $backing */
        $backing = (new ReflectionEnum($enum))
            ->getBackingType()
            ?->getName();

        $this->subtype($backing);
        $this->multiple($multiple);

        return $this;
    }

    /**
     * Set the filter to be for float values.
     *
     * @return $this
     */
    public function float(): static
    {
        $this->type(self::NUMBER);
        $this->asFloat();

        return $this;
    }

    /**
     * Set the filter to be for integer values.
     *
     * @return $this
     */
    public function int(): static
    {
        $this->type(self::NUMBER);
        $this->asInt();

        return $this;
    }

    /**
     * Set the filter to be for multiple values.
     *
     * @return $this
     */
    public function multiple(bool $value = true): static
    {
        $this->type(self::SELECT);
        $this->asArray();
        $this->setMultiple($value);

        return $this;
    }

    /**
     * Set the filter to be for text values.
     *
     * @return $this
     */
    public function text(): static
    {
        $this->type(self::TEXT);
        $this->asString();

        return $this;
    }

    /**
     * Set the filter to be for time values.
     *
     * @return $this
     */
    public function time(): static
    {
        $this->type(self::TIME);
        $this->asTime();

        return $this;
    }

    /**
     * Set the filter to respond only to presence values.
     *
     * @return $this
     */
    public function presence(): static
    {
        $this->asBoolean();
        $this->presence = true;

        return $this;
    }

    /**
     * Determine if the filter only responds to presence values.
     */
    public function isPresence(): bool
    {
        return $this->presence;
    }

    /**
     * Handle refining the query.
     *
     * @param  TBuilder  $query
     * @param  mixed  $value
     * @return bool
     */
    public function handle($query, $value)
    {
        $value = $this->transformParameter($value);

        $this->value($value);

        $this->checkIfActive($value);

        if (! $this->isActive() || ! $this->validate($value)) {
            return false;
        }

        return $this->refine($query, [
            ...$this->getBindings($query),
            'operator' => $this->getOperator(),
            'value' => $value,
        ]);
    }

    /**
     * Add a filter scope to the query.
     *
     * @param  TBuilder  $query
     * @param  string  $column
     * @param  string|null  $operator
     * @param  mixed  $value
     * @return void
     */
    public function apply($query, $column, $operator, $value)
    {
        match (true) {
            in_array($operator, ['LIKE', 'NOT LIKE', 'ILIKE', 'NOT ILIKE']) &&
                is_string($value) => Search::searchWildcard(
                    $query,
                    $value,
                    $column,
                    operator: $operator // @phpstan-ignore argument.type
                ),

            $this->isMultiple() ||
                $this->interpretsArray() => $query->whereIn($column, $value),

            $this->interpretsDate() =>
                // @phpstan-ignore-next-line
                $query->whereDate($column, $operator, $value),

            $this->interpretsTime() =>
                // @phpstan-ignore-next-line
                $query->whereTime($column, $operator, $value),

            default => $query->where($column, $operator, $value),
        };
    }

    /**
     * Get the representation of the instance.
     *
     * @return array<string, mixed>
     */
    protected function representation(): array
    {
        $value = $this->getValue();

        if ($value instanceof CarbonInterface) {
            $value = $value->format('Y-m-d\TH:i:s');
        }

        return array_merge(parent::representation(), [
            'type' => $this->getType(),
            'value' => $this->getNormalizedValue(),
            'options' => $this->optionsToArray(),
        ]);
    }

    /**
     * Transform the value for the filter.
     */
    protected function transformParameter(mixed $value): mixed
    {
        $transformed = match (true) {
            filled($this->getOptions()) => $this->activateOptions($value),
            $this->isPresence() => $value ?: null,
            default => $value,
        };

        return $transformed ?? $this->getDefault();
    }

    /**
     * Determine if the filter is active.
     */
    protected function checkIfActive(mixed $value): void
    {
        $this->active(filled($value));
    }
}
