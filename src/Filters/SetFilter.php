<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use Honed\Refine\Filters\Concerns\Option;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SetFilter extends Filter
{
    use Concerns\HasOptions;
    use Concerns\IsMultiple;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->type('set');
    }

    /**
     * {@inheritdoc}
     */
    public function isActive(): bool
    {
        if (! $this->isMultiple()) {
            return parent::isActive();
        }

        $value = $this->getValue();

        return parent::isActive()
            && \is_array($value)
            && filled($value);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Builder $builder, Request $request): bool
    {
        $rawValue = $this->getValueFromRequest($request);

        $options = \array_values(\array_filter(
            $this->getOptions(),
            fn (Option $option) => $option->active(
                \in_array(
                    $option->getValue(),
                    (array) $rawValue,
                    true
                ))->isActive(),
        ));

        $value = match (true) {
            $this->isMultiple() => \array_map(
                fn (Option $option) => $option->getValue(),
                $options),
            default => \array_shift($options)?->getValue(),
        };

        $this->value($value);

        if (! $this->isActive() || ! $this->validate($value)) {
            return false;
        }

        $property = type($this->getAttribute())->asString();

        if ($this->isMultiple()) {
            $this->handleMultiple(
                $builder,
                type($value)->asArray(),
                $property
            );

            return true;
        }

        /**
         * @var string|int|float $value
         */
        parent::handle($builder, $value, $property);

        return true;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<int,string|int|float>  $value
     */
    private function handleMultiple(Builder $builder, array $value, string $property): void
    {
        $column = $builder->qualifyColumn($property);

        $builder->whereIn(
            column: $column,
            values: $value,
            boolean: 'and'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return \array_merge(parent::toArray(), [
            'multiple' => $this->isMultiple(),
            'options' => $this->optionsToArray(),
        ]);
    }

    /**
     * @return array<int,string|int|float>|string|int|float|null
     */
    public function getValueFromRequest(Request $request): mixed
    {
        $value = parent::getValueFromRequest($request);

        if (! $this->isMultiple()) {
            /**
             * @var string|int|float|null $value
             */
            return $value;
        }

        return \array_map(
            fn ($v) => \trim($v),
            \explode(',', (string) $value)
        );
    }
}
