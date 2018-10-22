<?php

namespace Larangular\RoutingController;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;


trait Model {

    public function hasColumn($column){
        $columns = $this->getColumns();
        $search = array_search($column, $columns);
        return ($search > -1);
    }

    public function getColumns(){
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->table);
    }

    public function setFillables($data){
        $fillables = $this->getFillable();
        foreach($fillables as $fillable){
            if(!array_key_exists($fillable, $data) || $data[$fillable] === '') continue;
            $col_data = $data[$fillable];
            $this->setProperty($fillable, $col_data);
        }
    }

    public static function clean($data, $model, $appendId = true){
        //$model = 'App\\Models\\'.$model;
        $object = new $model();
        $fillables = $object->getFillable();

        foreach($data as $key => $value){
            if(array_search($key, $fillables) !== false || ($key == 'id' && $appendId)) continue;
            unset($data[$key]);
        }
        return $data;
    }

    public function setProperty($name, $value){
        $method = 'set'.ucfirst($name).'Attribute';
        //if (is_array($value)) $value = json_encode($value);
        if(method_exists($this, $method)){
            $this->{$method}($value);
            //call_user_func($this->{$method}, $value);
        }else{
            $this[$name] = $value;
        }
    }

    static public function hydrate(array $data, $connection = NULL)
    {
        // get calling class so we can hydrate using that type
        $klass = get_called_class();

        // psuedo hydrate
        $collection = new Collection();
        foreach ($data as $raw_obj)
        {
            $model = new $klass;
            $model = $model->newFromBuilder($raw_obj);
            if (!is_null($connection))
                $model = $model->setConnection($connection);
            $collection->add($model);
        }
        return $collection;

    }

}
