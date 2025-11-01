<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use Closure;
use Honed\Refine\Concerns\CanBeNullable;
use Honed\Refine\Option;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filters\Filter<TModel, TBuilder>
 */
class TernaryFilter extends Filter
{
    use CanBeNullable;

    /**
     * The label for the default option.
     *
     * @var string
     */
    protected $blankLabel = 'All';

    /**
     * The query to apply when the placeholder is selected.
     *
     * @var (Closure(TBuilder):mixed)|null
     */
    protected $blankQuery = null;

    /**
     * The label for the true option.
     *
     * @var string
     */
    protected $trueLabel = 'True';

    /**
     * The query to apply when the true option is selected.
     *
     * @var (Closure(TBuilder):mixed)|null
     */
    protected $trueQuery = null;

    /**
     * The label for the false option.
     *
     * @var string
     */
    protected $falseLabel = 'False';

    /**
     * The query to apply when the false option is selected.
     *
     * @var (Closure(TBuilder):mixed)|null
     */
    protected $falseQuery = null;

    /**
     * Provide the instance with any necessary setup.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->type('select');

        $this->defaultToBlank();

        $this->query(
            fn ($query, $value = null) => match ($value) {
                'true' => $this->callTrueQuery($query),
                'false' => $this->callFalseQuery($query),
                default => $this->callBlankQuery($query),
            }
        );
    }

    /**
     * Set the default to the blank option.
     */
    public function defaultToBlank(): static
    {
        return $this->default('blank');
    }

    /**
     * Set the label for the blank option.
     *
     * @return $this
     */
    public function blankLabel(string $label): static
    {
        $this->blankLabel = $label;

        return $this;
    }

    /**
     * Get the label for the blank option.
     */
    public function getBlankLabel(): string
    {
        return $this->blankLabel;
    }

    /**
     * Set the query to apply when the blank is selected.
     *
     * @param  (Closure(TBuilder):mixed)|null  $query
     * @return $this
     */
    public function blankQuery(?Closure $query): static
    {
        $this->blankQuery = $query;

        return $this;
    }

    /**
     * Get the query to apply when the blank is selected.
     *
     * @return (Closure(TBuilder):mixed)|null
     */
    public function getBlankQuery(): ?Closure
    {
        return $this->blankQuery;
    }

    /**
     * Execute the query to apply when the blank is selected.
     *
     * @param  TBuilder  $builder
     */
    public function callBlankQuery(Builder $builder): mixed
    {
        $callback = $this->getBlankQuery()
            ?? fn ($query) => $query;

        return $callback($builder);
    }

    /**
     * Set the default to the true option.
     */
    public function defaultToTrue(): static
    {
        return $this->default('true');
    }

    /**
     * Set the label for the true option.
     *
     * @return $this
     */
    public function trueLabel(string $label): static
    {
        $this->trueLabel = $label;

        return $this;
    }

    /**
     * Get the label for the true option.
     */
    public function getTrueLabel(): string
    {
        return $this->trueLabel;
    }

    /**
     * Set the query to apply when the true option is selected.
     *
     * @param  (Closure(TBuilder):mixed)|null  $query
     * @return $this
     */
    public function trueQuery(?Closure $query): static
    {
        $this->trueQuery = $query;

        return $this;
    }

    /**
     * Get the query to apply when the true option is selected.
     *
     * @return (Closure(TBuilder):mixed)|null
     */
    public function getTrueQuery(): ?Closure
    {
        return $this->trueQuery;
    }

    /**
     * Execute the query to apply when the true option is selected.
     *
     * @param  TBuilder  $builder
     */
    public function callTrueQuery(Builder $builder): mixed
    {
        $callback = match (true) {
            (bool) ($q = $this->getTrueQuery()) => $q,
            $this->isNullable() => fn ($query) => $query->whereNot($this->getQualifiedName($query), null),
            default => fn ($query) => $query->where($this->getQualifiedName($query), true)
        };

        return $callback($builder);
    }

    /**
     * Set the default to the false option.
     */
    public function defaultToFalse(): static
    {
        return $this->default('false');
    }

    /**
     * Set the label for the false option.
     *
     * @return $this
     */
    public function falseLabel(string $label): static
    {
        $this->falseLabel = $label;

        return $this;
    }

    /**
     * Get the label for the false option.
     */
    public function getFalseLabel(): string
    {
        return $this->falseLabel;
    }

    /**
     * Set the query to apply when the false option is selected.
     *
     * @param  (Closure(TBuilder):mixed)|null  $query
     * @return $this
     */
    public function falseQuery(?Closure $query): static
    {
        $this->falseQuery = $query;

        return $this;
    }

    /**
     * Get the query to apply when the false option is selected.
     *
     * @return (Closure(TBuilder):mixed)|null
     */
    public function getFalseQuery(): ?Closure
    {
        return $this->falseQuery;
    }

    /**
     * Execute the query to apply when the false option is selected.
     *
     * @param  TBuilder  $builder
     */
    public function callFalseQuery(Builder $builder): mixed
    {
        $callback = match (true) {
            (bool) ($q = $this->getFalseQuery()) => $q,
            $this->isNullable() => fn ($query) => $query->where($this->getQualifiedName($query), null),
            default => fn ($query) => $query->where($this->getQualifiedName($query), false)
        };

        return $callback($builder);
    }

    /**
     * Set how the query should change for each state of the ternary filter.
     *
     * @param  (Closure(TBuilder):mixed)|null  $true
     * @param  (Closure(TBuilder):mixed)|null  $false
     * @param  (Closure(TBuilder):mixed)|null  $blank
     * @return $this
     */
    public function queries(
        ?Closure $true = null,
        ?Closure $false = null,
        ?Closure $blank = null
    ): static {

        $this->trueQuery($true);
        $this->falseQuery($false);
        $this->blankQuery($blank);

        return $this;
    }

    /**
     * Get the options for the filter.
     *
     * @return array<int, Option>
     */
    public function getOptions(): array
    {
        return [
            Option::make('blank', $this->getBlankLabel()),
            Option::make('true', $this->getTrueLabel()),
            Option::make('false', $this->getFalseLabel()),
        ];
    }
}
