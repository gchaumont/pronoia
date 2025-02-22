<?php

namespace Pronoia\Sorting;

use Illuminate\Database\Eloquent\Builder;

class SortOption implements \JsonSerializable
{
    public mixed $default = null;

    public string $label;

    public \Closure $query;

    public function __construct(public readonly string $name) {}

    public static function make(...$args): static
    {
        return new static(...$args);
    }

    public function applySorting(Builder $query): Builder
    {
        if (isset($this->query)) {
            return ($this->query)($query);
        }

        return $query;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function apply(\Closure $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'value' => $this->name,
            'label' => $this->label ?? $this->name,
        ];
    }
}
