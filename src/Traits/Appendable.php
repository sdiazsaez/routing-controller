<?php

namespace Larangular\RoutingController\Traits;

use Illuminate\Support\Facades\Request;

trait Appendable {
    protected $appendParameterName = 'appends';

    public function appends($attributes) {
        $appendables = $this->getWithAppendsList();
        return $this->append($appendables);
    }

    protected function getWithAppendsList($relations = null) {
        return $relations
            ? (array)$relations
            : (array)Request::get($this->appendParameterName, []);
    }

}

