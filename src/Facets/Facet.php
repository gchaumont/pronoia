<?php

namespace App\Support\Search\Facets;

use App\Support\Search\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filters items and provieds facet options.
 */
class Facet extends Filter
{
    public null|string $field;

    public null|string $label;

    public \Closure|array $options;

    public \Closure $prepareQuery;

    public function prepare(Builder $query): Builder
    {
        return isset($this->prepareQuery)
            ? ($this->prepareQuery)($query)
            : $query;
    }

    public function prepareQuery(\Closure $prepare): static
    {
        $this->prepareQuery = $prepare;

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function field(string $field): static
    {
        return $this->query(fn (Builder $query, mixed $value) => $query->where($field, $value));
    }

    public function options(\Closure|array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions($response): mixed
    {
        if ($this->options instanceof \Closure) {
            return ($this->options)($response);
        }

        return $this->options;
    }
}
