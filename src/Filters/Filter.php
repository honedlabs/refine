<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use Honed\Core\Concerns\Validatable;
use Honed\Refine\Refiner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Filter extends Refiner
{
    use Validatable;

    const Is = '=';

    const Not = '!=';

    const GreaterThan = '>';

    const LessThan = '<';

    const Exact = 'exact';

    const Like = 'like';

    const StartsWith = 'starts_with';

    const EndsWith = 'ends_with';

    /**
     * The database filter to use.
     *
     * @var string
     */
    protected $mode = self::Exact;

    /**
     * The operator to use.
     *
     * @var string
     */
    protected $operator = self::Is;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->type('filter');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return \array_merge(parent::toArray(), [
            'value' => $this->getValue(),
        ]);
    }

    /**
     * Apply the filter to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    public function apply(Builder $builder, Request $request): bool
    {
        /**
         * @var string|int|float|null
         */
        $value = $this->getValueFromRequest($request);

        $this->value($value);

        if (! $this->isActive() || ! $this->validate($value)) {
            return false;
        }

        /** @var string */
        $attribute = $this->getAttribute();

        $this->handle($builder, $value, $attribute);

        return true;
    }

    /**
     * Execute the filter as a query on the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    public function handle(Builder $builder, mixed $value, string $property): void
    {
        $column = $builder->qualifyColumn($property);

        if ($this->getMode() === self::Exact) {
            $builder->where(
                column: $column,
                operator: $this->getOperator(),
                value: $value,
                boolean: 'and'
            );

            return;
        }

        $operator = match (\mb_strtolower($operator = $this->getOperator())) {
            '=', 'like' => 'LIKE',
            '!=', 'not like' => 'NOT LIKE',
            default => throw new \InvalidArgumentException("Invalid operator [{$operator}] provided for [{$property}] filter.")
        };

        $sql = match ($this->getMode()) {
            self::StartsWith => "{$column} {$operator} ?",
            self::EndsWith => "{$column} {$operator} ?",
            default => "LOWER({$column}) {$operator} ?",
        };

        $bindings = match ($this->getMode()) {
            self::StartsWith => ["{$value}%"], // @phpstan-ignore-line
            self::EndsWith => ["%{$value}"], // @phpstan-ignore-line
            default => ['%'.mb_strtolower((string) $value, 'UTF8').'%'], // @phpstan-ignore-line
        };

        $builder->whereRaw(
            sql: $sql,
            bindings: $bindings,
            boolean: 'and'
        );
    }

    /**
     * Retrieve the filter value from the request.
     *
     * @return string|int|float|null
     */
    public function getValueFromRequest(Request $request)
    {
        return $request->input($this->getParameter()); // @phpstan-ignore-line
    }

    public function isActive(): bool
    {
        return $this->hasValue();
    }

    /**
     * @return $this
     */
    public function mode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return $this
     */
    public function exact(): static
    {
        return $this->mode(self::Exact);
    }

    /**
     * @return $this
     */
    public function like(): static
    {
        return $this->mode(self::Like);
    }

    /**
     * @return $this
     */
    public function startsWith(): static
    {
        return $this->mode(self::StartsWith);
    }

    /**
     * @return $this
     */
    public function endsWith(): static
    {
        return $this->mode(self::EndsWith);
    }

    /**
     * @return $this
     */
    public function operator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * @return $this
     */
    public function not(): static
    {
        return $this->operator(self::Not);
    }

    /**
     * @return $this
     */
    public function gt(): static
    {
        return $this->operator(self::GreaterThan);
    }

    /**
     * @return $this
     */
    public function lt(): static
    {
        return $this->operator(self::LessThan);
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }
}
