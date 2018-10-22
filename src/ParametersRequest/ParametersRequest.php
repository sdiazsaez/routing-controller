<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 9/26/18
 * Time: 09:57
 */

namespace Larangular\RoutingController\ParametersRequest;

use Illuminate\Support\Str;

class ParametersRequest {

    static function filter(&$parameters, $keywords = []): array {
        $response = [];
        if (count($parameters) > 0) {
            foreach ($keywords as $keyword) {
                if (array_key_exists($keyword, $parameters)) {

                    $response[$keyword] = $parameters[$keyword];

                    if (Str::contains($response[$keyword], ',')) {
                        /**
                         * GET /resource?operation=values&...
                         * operation(values)
                         */
                        $response[$keyword] = explode(',', $response[$keyword]);
                    }

                    unset($parameters[$keyword]);
                }
            }
        }

        return $response;
    }

}
