<?php

namespace Larangular\RoutingController;

use Larangular\RoutingController\{Contracts\RecursiveStoreable,
    ParametersRequest\QueryBuilderRequest,
    RecursiveStore\RecursiveStore,
    MethodRequest\MethodRequest,
    PaginableRequest,
    Model as RoutingModel};
use Larangular\Support\Facades\Instance;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Foundation\ {Bus\DispatchesJobs,
    Validation\ValidatesRequests,
    Auth\Access\AuthorizesRequests};
use \Illuminate\Database\Eloquent\{Model,
    Builder,
    Collection};

class Controller extends BaseController {
    use RoutingRequests, RecursiveStore, QueryBuilderRequest, PaginableRequest, MethodRequest, MakeResponse, AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $getMethods = ['count'];
    private   $instanceModel;

    public function __construct() {
        $this->getModel();
    }

    /**
     * Routing request methods
     */
    public function allowedMethods() {
        return [
            'index',
            'show',
            'store',
            'update',
            'destroy',
        ];
    }

    public function entries($where = []) {
        $queryParameters = $this->queryBuilderRequestParameters($where);
        $pagination = $this->paginableRequestParameters($where);

        $query = $this->getEntries($where);
        $query = $this->queryBuilderRequest($query, $queryParameters);
        $query = $this->paginableRequestApply($query, $pagination);
        $query = $this->withRelations($query);

        return $this->makeResponse($this->queryFetch($query));
    }

    public function entry($id) {
        $query = $this->getEntry($id);
        return $this->makeResponse($this->queryFetch($query));
    }

    public function save($data) {
        if (Instance::hasInterface($this, RecursiveStoreable::class)) {
            $data = $this->recursiveStore($data);
        }
        $response = $this->modelStore($data);
        return $this->modelRequest('find', $response->id);
    }

    public function delete($id) {
        $object = $this->modelRequest('find', $id);
        if (!$object) return $this->_error();
        $canSoftDelete = (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($object)));
        $object->delete();

        $response = [
            'data' => [
                'id'         => $object->id,
                'trashed'    => ($canSoftDelete)
                    ? $object->trashed()
                    : true,
                'softdelete' => $canSoftDelete,
            ],
        ];

        return $response;
    }


    public function search($column, $value) {
        $data = [
            [
                $column,
                'LIKE',
                "%$value%",
            ],
        ];
        return $this->modelRequest('where', $data)
                    ->get();
    }

    protected function getEntries($where = []) {
        $method = 'query';

        if (count($where) > 0) {
            $model = $this->getModel();
            if (Instance::hasTrait($model, 'Jedrzej\Searchable\SearchableTrait')) {
                $method = 'filtered';
            } else {
                $method = 'where';
                $cols = array_keys($where);
                foreach ($cols as $col) {
                    if (!$this->modelInstanceRequest('hasColumn', $col)) {
                        unset($where[$col]);
                    } elseif (Str::contains($where[$col], ',')) {
                        /**
                         * GET /resource?column=operator,value&...
                         * Where([column, operator, value])
                         */
                        $value = [$col];
                        $value = array_merge($value, explode(',', $where[$col]));

                        unset($where[$col]);
                        $where[] = $value;
                    }
                }
            }
        }
        $response = (count($where) > 0)
            ? $this->modelRequest($method, $where)
            : $this->modelRequest($method);
        return $response;
    }

    protected function getEntry($id) {
        return $this->modelRequest('find', $id);
    }

    protected function modelStore(array $data) {
        $data = RoutingModel::clean($data, $this->model);
        $object = $this->getObject($data);

        if (method_exists($object, 'setFillables')) {
            $object->setFillables($data);
        } else {
            return $this->_error();
        }
        //dd($object->content, $data);

        $object->save();
        return $object;
    }

    public function getObject(array $data) {
        $method = '';
        $q = null;
        $id = (isset($data['id'])
            ? $data['id']
            : false);

        if ($id !== false) {
            $method = 'find';
            $q = $id;
        } else {
            $method = 'create';
            $q = $data;
        }

        $model = $this->modelRequest($method, $q);
        if (is_null($model)) {
            $model = $this->modelRequest('create', $data);
        }
        return $model;
    }

    private function instanceModel(): Model {
        $model = $this->getModel();
        if (!isset($this->instanceModel)) $this->instanceModel = new $model;
        return $this->instanceModel;
    }

    private function modelInstanceRequest($method, $data = null) {
        return (is_null($data))
            ? \call_user_func([
                    $this->instanceModel(),
                    $method,
                ])
            : \call_user_func([
                $this->instanceModel(),
                $method,
            ], $data);
    }

    private function modelRequest($method, $data = null) {
        //TODO implement pagination
        $query = (is_null($data))
            ? \call_user_func($this->getModel() . '::' . $method)
            : \call_user_func($this->getModel() . '::' . $method, $data);

        return $query;
    }

    protected function queryFetch($query) {
        if (Instance::instanceOf($query, Builder::class)) {
            $query = $query->get();
        }
        return $query;
    }

    private function getModel() {
        if (!isset($this->model)) {
            $this->model = $this->model();
        }

        return $this->model;
    }

    protected function isEditing($data): bool {
        return (array_key_exists('id', $data) && $data['id'] > 0);
    }


    protected function _error($message = '') {
        return [
            'data' => [
                'status'  => false,
                'message' => $message,
                'debug'   => debug_backtrace(false, 3),
            ],
        ];
    }

    private function customRequest(Request $request) {
        $where = $request->all();
        unset($where['func']);
        return $where;
    }

    public function count(Request $request) {
        return $this->modelRequest('count');
    }

    public function newest(Request $request) {
        $key = 'updated_at';
        return $this->instanceModel()
                    ->where('updated_at', '>', $request->input($key))
                    ->get();
        return ($request->has($key))
            ? $this->instanceModel()
                   ->where('updated_at', '>', $request->input($key))
                   ->get()
            : $this->instanceModel()
                   ->all();
        //return $this->modelRequest('where', ['updated_at' => ['>', '2017-01-22 12:30:50']])->get();
    }

    private function requestCanBePaginated($method) {
        return ($method === 'where' || $method === 'all');
    }

    private function withRelations(&$query) {
        if (Instance::hasTrait($this->getModel(), 'Jedrzej\Withable\WithableTrait') && Instance::instanceOf($query, Builder::class)) {
            $query = $query->withRelations();
        }

        return $query;
    }
}

