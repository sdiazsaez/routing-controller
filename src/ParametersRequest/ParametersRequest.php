<?php

namespace Larangular\RoutingController\ParametersRequest;

use Illuminate\Support\Str;

/**
 * Utility class for extracting and transforming specified parameters from a request array.
 */
class ParametersRequest {

    /**
     * Extracts specified parameters from the input array and transforms comma-separated values into arrays.
     *
     * Matched keys are removed from the original $parameters array and returned in the result.
     * If a matched value contains commas (e.g., "a,b,c"), it will be split into an array (["a", "b", "c"]).
     *
     * @param array $parameters Reference to the parameter array to filter.
     * @param array $keywords   Keys to extract from the parameters.
     *
     * @return array Filtered parameters.
     */
    public static function filter(array &$parameters, array $keywords = []): array {
        if (empty($parameters) || empty($keywords)) {
            return [];
        }

        $response = [];

        foreach ($keywords as $keyword) {
            if (!array_key_exists($keyword, $parameters)) {
                continue;
            }

            $value = $parameters[$keyword];

            // Normalize to string before checking for commas
            if (is_string($value) && Str::contains($value, ',')) {
                $value = explode(',', $value);
            }

            $response[$keyword] = $value;
            unset($parameters[$keyword]);
        }

        return $response;
    }
}
