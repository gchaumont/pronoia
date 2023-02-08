<?php

namespace App\Support\Search\Facets;

class ToggleFacet extends Facet
{
    public \Closure|array $options = [true, false];
}
