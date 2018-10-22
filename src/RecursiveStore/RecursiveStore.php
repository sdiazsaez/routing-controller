<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 5/29/18
 * Time: 17:56
 */

namespace Larangular\RoutingController\RecursiveStore;

trait RecursiveStore {

    public function recursiveStore($data) {
        foreach($this->recursiveOptions() as $option) {
            if(!isset($data[$option->resource_key])) continue;
            $entry = $this->recursiveStore_saveResource($option, $data);
            $data[$option->identifier_key] = $entry->id;
        }

        return $data;
    }

    private function recursiveStore_saveResource($options, $data) {
        $instance = new $options->gateway();
        return $instance->save($data[$options->resource_key]);
    }
}
