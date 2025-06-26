<?php

use Larangular\RoutingController\Traits\HandlesSorting;

return [

    'default_sortable' => ['id', 'created_at'],
    'default_unsortable' => ['password', 'remember_token'],

    'reserved_query_keywords' => [
        'orderBy' => [
            'method' => 'applyOrderBy',
            'input_key' => 'orderBy',
            'guard_trait' => HandlesSorting::class,
        ],
        'orderByDesc' => 'orderByDesc',
        'limit' => 'limit',
        'with' => 'with',
        'trashed' => 'trashed',
        'count' => 'count',
        'select' => 'select',
        'where' => 'where',
    ]

];
