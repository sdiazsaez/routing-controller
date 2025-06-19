<?php

namespace Larangular\RoutingController\ParametersRequest;

use Illuminate\Database\Eloquent\Builder;

trait QueryBuilderRequest {

    protected function queryBuilderRequestParameters(&$parameters): array {
        return ParametersRequest::filter($parameters, array_keys(config('routing-controller.reserved_query_keywords', [])));
    }

    protected function queryBuilderRequest(Builder &$query, $parameters) {
        $keywords = config('routing-controller.reserved_query_keywords', []);

        foreach ($keywords as $keyword => $config) {
            // Allow flat format: 'limit' => 'limit'
            if (is_string($config)) {
                $method = $config;
                $inputKey = $keyword;
                $guardTrait = null;
                $guard = null;
            } else {
                $method = $config['method'] ?? $keyword;
                $inputKey = $config['input_key'] ?? $keyword;
                $guardTrait = $config['guard_trait'] ?? null;
                $guard = $config['guard'] ?? null;
            }

            if (!array_key_exists($inputKey, $parameters)) {
                continue;
            }

            // Only get the model if there's a guard to check
            if ($guardTrait || is_callable($guard)) {
                $model = $query->getModel();

                if ($guardTrait && !in_array($guardTrait, class_uses_recursive($model))) {
                    continue;
                }

                if (is_callable($guard) && !$guard($model)) {
                    continue;
                }
            }

            if (! method_exists($query, $method) && ! $query->hasNamedScope($method) && !is_callable([$query, $method])) {
                continue;
            }

            $query = $query->$method($parameters[$inputKey]);
        }

        return $query;
    }

}
