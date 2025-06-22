<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use BackedEnum;
use Carbon\CarbonInterface;
use Honed\Core\Concerns\HasValue;
use Honed\Core\Concerns\InterpretsRequest;
use Honed\Core\Concerns\Validatable;
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
    use Concerns\HasOperator;
    use Concerns\HasOptions {
        multiple as protected setMultiple;
    }
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
     * Whether the filter only responds to presence values.
     *
     * @var bool
     */
    protected $presence = false;

    /**
     * The default value to use for the filter even if it is not active.
     *
     * @var mixed
     */
    protected $default;

    /**
     * Provide the instance with any necessary setup.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->type('filter');

        $this->definition($this);
    }

    /**
     * Set the filter to be for boolean values.
     *
     * @return $this
     */
    public function boolean()
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
    public function date()
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
    public function datetime()
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
    public function float()
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
    public function int()
    {
        $this->type(self::NUMBER);
        $this->asInt();

        return $this;
    }

    /**
     * Set the filter to be for multiple values.
     *
     * @param  bool  $multiple
     * @return $this
     */
    public function multiple($multiple = true)
    {
        $this->type(self::SELECT);
        $this->asArray();
        $this->setMultiple($multiple);

        return $this;
    }

    /**
     * Set the filter to be for text values.
     *
     * @return $this
     */
    public function text()
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
    public function time()
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
    public function presence()
    {
        $this->asBoolean();
        $this->presence = true;

        return $this;
    }

    /**
     * Determine if the filter only responds to presence values.
     *
     * @return bool
     */
    public function isPresence()
    {
        return $this->presence;
    }

    /**
     * Set a default value to use for the filter if the filter is not active.
     *
     * @param  mixed  $default
     * @return $this
     */
    public function default($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get the default value to use for the filter if the filter is not active.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
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
     * {@inheritdoc}
     */
    public function toArray()
    {
        $value = $this->getValue();

        if ($value instanceof CarbonInterface) {
            $value = $value->format('Y-m-d\TH:i:s');
        }

        return array_merge(parent::toArray(), [
            'value' => $value,
            'options' => $this->optionsToArray(),
        ]);
    }

    /**
     * Define the filter instance.
     *
     * @param  $this  $filter
     * @return $this
     */
    protected function definition(self $filter): self
    {
        return $filter;
    }

    /**
     * Transform the value for the filter.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function transformParameter($value)
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
     *
     * @param  mixed  $value
     * @return void
     */
    protected function checkIfActive($value)
    {
        $this->active(filled($value));
    }
}
