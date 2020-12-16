<?php

namespace Larangular\RoutingController;

use Illuminate\Http\Resources\Json\JsonResource as BaseResource;

class Resource extends BaseResource {
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) {
        return parent::toArray($request);
    }

    public function withResponse($request, $response) {
        $response->header('Content-Type', 'application/vnd.api+json');
    }
}
