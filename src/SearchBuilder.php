<?php

namespace Pronoia;

use App\Support\Internationalisation\Locale;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

class SearchBuilder implements Responsable
{
    use ForwardsCalls;

    protected \Closure|string $searchable;

    protected string $resource;

    protected \Closure $transform;

    /**
     * Aggregated and filterable
     * by user.
     */
    protected array $facets = [];

    protected array $suggestions = [];

    /**
     * Filters hidden from user.
     */
    protected array $filters = [];

    protected bool|\Closure $cache;

    protected $cache_ttl;

    protected $pagination;

    public function __construct(protected Builder $builder)
    {
    }

    public function __call($method, $parameters)
    {
        $this->forwardCallTo($this->builder, $method, $parameters);

        return $this;
    }

    public function searchable(string|\Closure $searchable): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public static function for(Builder|string $builder): static
    {
        if (!$builder instanceof Builder) {
            $builder = (new $builder())->query();
        }

        return new static($builder);
    }

    public function resource(string $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    public function facets(array $facets): static
    {
        $this->facets = $facets;

        return $this;
    }

    public function suggestions(array $suggestions): static
    {
        $this->suggestions = $suggestions;

        return $this;
    }

    public function filters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function defaultSort(string $name): static
    {
        $this->defaultSort = $name;

        return $this;
    }

    public function cache(bool|callable $cache, $ttl = 60 * 10): static
    {
        $this->cache = $cache;
        $this->cache_ttl = $ttl;

        return $this;
    }

    public function paginate(...$args): static
    {
        $this->pagination = $args;

        return $this;
    }

    public function toResponse($request): JsonResponse
    {
        if (isset($this->cache) && (is_callable($this->cache) ? ($this->cache)($request) : $this->cache)) {
            return Cache::remember(
                key: implode('.', [
                    'searchbuilder',
                    $this->resource,
                    json_encode($request->query()),
                    Locale::active()->key(),
                ]),
                ttl: $this->cache_ttl,
                callback: fn () => $this->toResource($this->resolve())->toResponse($request),
            );
        }

        return $this->toResource($this->resolve())->toResponse($request);
    }

    public function toResource(Collection|AbstractPaginator $collection): JsonResource
    {
        if ($collection instanceof AbstractPaginator) {
            $hits = $collection->getCollection();
        } else {
            $hits = $collection;
        }
        if (isset($this->transform)) {
            $hits = ($this->transform)($hits);
        }

        return $this->resource::collection($hits)
            ->additional(['meta' => $this->getMeta($collection, $hits)]);
    }

    public function sortOptions(\Closure|array $options): static
    {
        $this->sortOptions = $options;

        return $this;
    }

    public function getSortOptions(): Collection
    {
        return collect($this->sortOptions ?? []);
    }

    public function transform(\Closure $transform): static
    {
        $this->transform = $transform;

        return $this;
    }

    public static function getCleanedQuery(?string $query)
    {
        $replace = [
            '\\' => '\\\\',
            '+' => '\\+',
            '-' => '\\-',
            '=' => '\\=',
            '&&' => '\\&\\&',
            '||' => '\\|\\|',
            '!' => '\\!',
            '(' => '\\(',
            ')' => '\\)',
            '{' => '\\{',
            '}' => '\\}',
            '[' => '\\[',
            ']' => '\\]',
            '^' => '\\^',
            '"' => '\\"',
            '~' => '\\~',
            '*' => '\\*',
            '?' => '\\?',
            ':' => '\\:',
            '/' => '\\/',
            'AND' => '\\A\\N\\D',
            'OR' => '\\O\\R',
            'NOT' => '\\N\\O\\T',
            // " " => "\ ",
            '>' => '\\ ',
            '<' => '\\ ',
        ];

        $query = str_replace(array_keys($replace), ' ', $query);

        return preg_replace('/\\s+/', ' ', $query);
    }

    protected function getMeta($result, $hits): array
    {
        return [
            'total' => method_exists($result, 'total') ? $result->total() : count($result),
            'sortOptions' => $this->getSortOptions(),
            'defaultSort' => $this->defaultSort ?? null,
            'facets' => collect($this->facets)
                ->filter(fn ($facet) => Str::is(explode(',', request()->input('facets')), $facet->name))
                ->keyBy(fn ($facet) => $facet->name)
                ->map(fn ($facet) => [
                    'label' => $facet->label ?? null,
                    'options' => $facet->getOptions($hits),
                ]),
            'suggestions' => collect($this->suggestions)
                ->filter(fn ($facet) => Str::is(explode(',', request()->input('suggest')), $facet->name))
                ->keyBy(fn ($facet) => $facet->name)
                ->map(fn ($facet) => [
                    'label' => $facet->label ?? null,
                    'options' => $facet->getOptions($hits),
                ]),
        ];
    }

    protected function getSort(): null|string
    {
        if (request()->has('sort')) {
            return request()->input('sort');
        }
        if (isset($this->defaultSort)) {
            return $this->getSortOptions()->firstWhere(fn ($o) => $o->name == $this->defaultSort)->name;
        }

        return $this->getSortOptions()->first()?->name;
    }

    protected function resolve()
    {
        if (isset($this->searchable) && request()->input('search')) {
            $this->builder = ($this->searchable)($this->builder, request()->input('search'));
        }

        foreach ($this->facets as $facet) {
            if (request()->has($facet->name)) {
                $facet->applyFilter($this->builder, request()->input($facet->name));
            }

            if (request()->has('facets') && Str::is(explode(',', request()->input('facets')), $facet->name)) {
                $facet->prepare($this->builder);
            }
        }


        if (request()->has('sort')) {
            collect($this->sortOptions)
                ->firstWhere(fn ($sort) => $sort->name == request()->input('sort'))
                ->applySorting($this->builder);
        } elseif (isset($this->defaultSort)) {
            $this->getSortOptions()
                ->firstWhere(fn ($o) => $o->name == $this->defaultSort)
                ?->applySorting($this->builder);
        }

        foreach ($this->filters as $filter) {
            if (request()->has($filter->name)) {
                $filter->applyFilter($this->builder, request()->input($filter->name));
            }
        }

        foreach ($this->suggestions as $suggestion) {
            if (request()->has('suggest') && Str::is(explode(',', request()->input('suggest')), $suggestion->name)) {
                $suggestion->prepare($this->builder);
            }
        }

        if (isset($this->pagination)) {
            $response = $this->builder
                ->paginate(...$this->pagination);
        }
        // response($this->builder->toSql())->send();

        $response ??= $this->builder->get();

        // response($response->response())->send();

        $corrected = 'correctedResponse';

        return $response;
    }
}
