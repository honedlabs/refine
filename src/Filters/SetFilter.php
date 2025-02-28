<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use Honed\Refine\Filters\Concerns\Option;

class SetFilter extends Filter
{
    use Concerns\HasOptions;
    use Concerns\IsMultiple;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->type('set');
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueKey()
    {
        return \sprintf('%s.%s',
            parent::getUniqueKey(),
            $this->isMultiple() ? 'multiple' : 'single'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
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
    public function apply($builder, $request)
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
     * Handle the case where the filter is a multiple filter.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<int,string|int|float>  $value
     * @param  string  $property
     * @return void
     */
    private function handleMultiple($builder, $value, $property)
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
    public function toArray()
    {
        return \array_merge(parent::toArray(), [
            'multiple' => $this->isMultiple(),
            'options' => $this->optionsToArray(),
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @return array<int,string|int|float>|string|int|float|null
     */
    public function getValueFromRequest($request)
    {
        $value = parent::getValueFromRequest($request);

        if (! $this->isMultiple()) {
            /** @var string|int|float|null $value */
            return $value;
        }

        return \array_map(
            fn ($v) => \trim($v),
            \explode(',', (string) $value)
        );
    }
}
