<?php

declare(strict_types=1);

namespace Honed\Refine\Searches;

use Honed\Refine\Enums\SearchMode;
use Honed\Refine\Refiner;
use Honed\Refine\Searches\Concerns\HasSearchMode;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Refiner<TModel, TBuilder>
 */
class Search extends Refiner
{
    use HasSearchMode;

    /**
     * The identifier to use for evaluation.
     *
     * @var string
     */
    protected $evaluationIdentifier = 'search';

    /**
     * The key column to select when using unions.
     *
     * @var ?string
     */
    protected $unionKey = null;

    /**
     * Perform a wildcard search on the query.
     *
     * @param  TBuilder  $query
     */
    public static function searchWildcard(
        Builder $query,
        string $term,
        string $column,
        string $boolean = 'and',
        string $operator = 'LIKE'
    ): void {

        $sql = "{$column} {$operator} ?";
        $binding = static::binding($term);

        $query->getQuery()
            ->whereRaw($sql, $binding, $boolean);
    }

    /**
     * Get the bindings for the search wildcard.
     *
     * @return list<string>
     */
    public static function binding(string $term): array
    {
        return ["%{$term}%"];
    }

    /**
     * Set the key column to select when using unions.
     *
     * @return $this
     */
    public function unionOn(string $column): static
    {
        $this->unionKey = $column;

        return $this;
    }

    /**
     * Get the key column to select when using unions.
     */
    public function getUnionKey(): ?string
    {
        return $this->unionKey;
    }

    /**
     * Union the search as a subquery.
     *
     * @param  TBuilder  $builder
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function unionAs(Builder $builder): Builder
    {
        $query = $builder->getModel()->newQuery();

        $column = $this->qualifyColumn(
            $this->getUnionKey() ?? $builder->getModel()->getKeyName(),
            $query
        );

        $query->getQuery()->select($column);

        if (is_string($table = $this->getQualifier())) {
            $query->getQuery()->from($table);
        }

        return $query;
    }

    /**
     * Handle the searching of the query.
     *
     * @param  TBuilder  $query
     * @param  array<int, string>|null  $columns
     */
    public function handle(Builder $query, ?string $term, ?array $columns, bool $or = false): bool
    {
        $this->checkIfActive($columns);

        if ($this->isNotActive() || ! $term) {
            return false;
        }

        return $this->refine($query, [
            ...$this->getBindings($query),
            'boolean' => $or ? 'or' : 'and',
            'search' => $term,
            'term' => $term,
        ]);
    }

    /**
     * Add a search scope to the query.
     *
     * @param  TBuilder  $query
     */
    public function apply(Builder $query, string $term, string $column, string $boolean = 'and'): void
    {
        $mode = $this->getSearchMode();

        match ($mode) {
            SearchMode::NaturalLanguage,
            SearchMode::Boolean => $this->asFullText(
                $query, $mode, $term, $column, $boolean
            ),
            default => $this->asWildcard(
                $query, $mode, $term, $column, $boolean
            ),
        };
    }

    /**
     * Apply a wildcard search to the query.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    protected function asWildcard(Builder $query, SearchMode $mode, string $term, string $column, string $boolean): void
    {
        $query->getQuery()
            ->whereRaw("{$column} LIKE ?", [$this->bind($term, $mode)], $boolean);
    }

    /**
     * Apply a full text index search to the query.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    protected function asFullText(Builder $query, SearchMode $mode, string $term, string $column, string $boolean): void
    {
        $options = $mode === SearchMode::Boolean ? ['mode' => 'boolean'] : [];

        $query->getQuery()
            ->whereFullText($column, $this->bind($term, $mode), $options, $boolean);
    }

    /**
     * Bind the term for searching.
     */
    protected function bind(string $term, SearchMode $mode): string
    {
        return match ($mode) {
            SearchMode::Wildcard => "%{$term}%",
            SearchMode::StartsWith => "{$term}%",
            SearchMode::EndsWith => "%{$term}",
            SearchMode::Boolean => $this->bindBoolean($term),
            default => $term,
        };
    }

    /**
     * Bind the term for boolean searching.
     */
    protected function bindBoolean(string $term): string
    {
        $words = preg_split('/\s+/', trim($term)) ?: [];

        return implode(' ', array_map(
            static fn (string $word) => "+{$word}*",
            array_filter(
                array_map(
                    static fn (string $word) => (string) preg_replace('/[^\p{L}\p{N}]/u', '', $word),
                    $words
                ),
                static fn ($word) => filled($word)
            )
        ));
    }

    /**
     * Determine if the search is active.
     *
     * @param  array<int,string>|null  $columns
     */
    protected function checkIfActive(?array $columns): void
    {
        $this->active(
            (! $columns) ?: in_array($this->getParameter(), $columns, true),
        );
    }
}
