<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 5/2/18
 * Time: 07:06
 */

namespace Larangular\RoutingController;

use Larangular\Support\Instance;
use Larangular\RoutingController\Contracts\HasResource;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Database\Eloquent\Collection;

trait MakeResponse {

    protected function makeResponse($data) {
        if ($this->requestAjax()) {
            $resource = Resource::class;
            if (Instance::hasInterface($this, HasResource::class)) {
                $resource = $this->resource();
            }
            return $this->makeResource($resource, $data);
        }
        return $data;
    }

    private function makeResource($resourceClass, $data) {
        if (Instance::instanceOf($data, Collection::class)) {
            return $resourceClass::collection($data);
        } else {
            return new $resourceClass($data);
        }
    }

    private function requestAjax() {
        return (RequestFacade::ajax() || RequestFacade::input('ajax'));
    }
}
