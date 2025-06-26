<?php

namespace Larangular\RoutingController\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HandlesSorting
{
    /**
     * Apply flexible sorting from a string like:
     *   name:desc,id,fase.status:asc
     */
    public function scopeApplyOrderBy(Builder $query, string $value): Builder
    {
        $parts = array_filter(explode(',', $value));

        foreach ($parts as $part) {
            [$column, $direction] = array_pad(explode(':', $part), 2, 'asc');
            $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

            if ($this->callSortInterceptor($query, $column, $direction)) {
                continue;
            }

            if (str_contains($column, '.')) {
                [$relation, $relColumn] = explode('.', $column, 2);

                if ($this->relationLoaded($relation)) {
                    $query->getQuery()->orderByRaw("`{$relation}`.`{$relColumn}` {$direction}");
                }

                continue;
            }

            if (in_array($column, $this->getSortableFields())) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }

    /**
     * Check if a custom sort handler exists and run it if so.
     */
    protected function callSortInterceptor(Builder $builder, string $field, string $direction): bool
    {
        $method = 'process' . str_replace(['.', ':'], '_', Str::studly($field)) . 'Sort';

        if (method_exists($this, $method)) {
            return $this->{$method}($builder, $direction) === true;
        }

        return false;
    }

    /**
     * Get merged sortable fields from model + config
     */
    protected function getSortableFields(): array
    {
        $model = $this;

        $defaults    = config('sorting.default_sortable', []);
        $blocked     = config('sorting.default_unsortable', []);
        $modelAllow  = property_exists($model, 'sortable') ? $model->sortable : [];
        $modelBlock  = property_exists($model, 'unsortable') ? $model->unsortable : [];

        return array_values(array_diff(
            array_merge($defaults, $modelAllow),
            array_merge($blocked, $modelBlock)
        ));
    }
}
