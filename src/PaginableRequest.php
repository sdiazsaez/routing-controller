<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 9/26/18
 * Time: 09:03
 */

namespace Larangular\RoutingController;

trait PaginableRequest {

    protected function paginableRequestParameters(&$parameters): array {
        $response = [];
        foreach($this->keywords() as $keyword) {
            if (array_key_exists($keyword, $parameters)) {
                $response[$keyword] = $parameters[$keyword];
                unset($parameters[$keyword]);
            }
        }

        return $response;
    }

    protected function paginableRequestApply(&$query, $parameters) {
        $paginateKey = array_first($this->keywords());
        if(array_key_exists($paginateKey, $parameters)) {
            $query = $query->paginate($parameters[$paginateKey]);
        }
        return $query;
    }

    private function keywords(): array {
        return [
            'paginate',
            'page'
        ];
    }

}
