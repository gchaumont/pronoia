<?php

namespace Pronoia\Facets;

class ToggleFacet extends Facet
{
    public \Closure|array $options = [true, false];
}
