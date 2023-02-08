<?php

namespace Pronoia\Filters;

use Illuminate\Database\Eloquent\Builder;

class Filter
{
    public mixed $default = null;

    public \Closure $query;

    public function __construct(public readonly string $name)
    {
        // $this->query ??= fn (Builder $builder, mixed $value) => $builder;
    }

    /**
     * Create the Facet.
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    public function applyFilter(Builder $query, mixed $value): Builder
    {
        return isset($this->query)
            ? ($this->query)($query, $value)
            : $query;
    }

    public function query(\Closure $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }
}
