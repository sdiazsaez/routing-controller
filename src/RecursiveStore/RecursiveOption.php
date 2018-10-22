<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 5/29/18
 * Time: 18:15
 */

namespace Larangular\RoutingController\RecursiveStore;

class RecursiveOption {

    public $identifier_key;
    public $resource_key;
    public $gateway;

    public function __construct($idKey, $gatewayNamespace, $resourceKey = null) {
        $this->identifier_key = $idKey;
        $this->resource_key = $this->getResourceKey($idKey, $resourceKey) ;
        $this->gateway = $gatewayNamespace;
    }

    private function getResourceKey($identifierKey, $resourceKey = null): string {
        if(is_null($resourceKey)) {
            $resourceKey = $this->makeDefaultResourceKey($identifierKey);
        }

        return $resourceKey;
    }

    /**
     * @param $identifierKey: string ex.: user_id
     * @return string ex.: user
     */
    private function makeDefaultResourceKey($identifierKey): string {
        return substr($identifierKey, 0, -3);
    }

}
