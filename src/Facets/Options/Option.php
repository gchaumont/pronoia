<?php

namespace Pronoia\Facets\Options;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Filters items and provieds facet options.
 */
class Option implements \JsonSerializable
{
    public function __construct(
        public string $value,
        public null|string $label = null,
        public null|string $img = null,
        public null|int|string $count = null,
        public null|array $options = null,
        public null|array|JsonResource $resource = null,
    ) {}

    public function jsonSerialize(): mixed
    {
        return array_filter([
            'value' => $this->value,
            'label' => $this->label,
            'img' => $this->img,
            'count' => $this->count,
            'options' => $this->options,
            'resource' => $this->resource,
        ]);
    }
}
