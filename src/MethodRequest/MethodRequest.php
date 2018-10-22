<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 7/4/18
 * Time: 13:01
 */

namespace Larangular\RoutingController\MethodRequest;

use Illuminate\Database\Eloquent\Builder;

trait MethodRequest {
    private function reservedKeys() {
        return [
            'method',
            'paginate',
            'page'
        ];
    }

    //: ?Builder
    protected function getReservedKeyQuery($data) {
        if($this->hasReservedKeys($data)) {
            return $this->callReservedKeyMethod($data);
        }
        return null;
    }

    private function hasReservedKeys($data): bool {
        $response = false;
        foreach ($this->reservedKeys() as $key) {
            if (!$response) {
                $response = array_key_exists($key, $data);
            }
        }

        return $response;
    }

    //: ?Builder
    private function callReservedKeyMethod($data) {
        $response = null;
        foreach ($this->reservedKeys() as $key) {
            if ($response === null && array_key_exists($key, $data)) {
                $method = $data[$key];
                unset($data[$key]);
                $response = $this->{$method}($data);
            }
        }

        return $response;
    }
}
