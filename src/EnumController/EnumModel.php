<?php

namespace Larangular\RoutingController\EnumController;

use Illuminate\Database\Eloquent\Model;

class EnumModel extends Model {

    protected $fillable = [
        'key',
        'value',
        'label'
        /*
        'type',
        'values'*/
    ];

}
