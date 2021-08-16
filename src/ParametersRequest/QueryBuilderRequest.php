<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 9/26/18
 * Time: 10:00
 */

namespace Larangular\RoutingController\ParametersRequest;

use Larangular\RoutingController\Contracts\IGatewayModel;
use Larangular\RoutingController\ParametersRequest\ParametersRequest;

trait QueryBuilderRequest {

    protected function queryBuilderRequestParameters(&$parameters): array {
        return ParametersRequest::filter($parameters, $this->queryBuilderKeywords());
    }

    protected function queryBuilderRequest(&$query, $parameters) {
        if (count($parameters) > 0) {
            foreach ($this->queryBuilderKeywords() as $keyword) {
                if (array_key_exists($keyword, $parameters)) {
                    $query = $query->{$keyword}($parameters[$keyword]);
                }
            }
        }

        return $query;
    }

    private function queryBuilderKeywords(): array {
        return [
            'orderBy',
            'orderByDesc',
            'where',
            'select',
            'limit',
            'with',
            'trashed',
            'count'
        ];
    }

}
