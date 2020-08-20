<?php

namespace Larangular\RoutingController;

use Illuminate\Database\Eloquent\Collection;


trait Model {

    public function hasColumn(string $column): bool {
        $columns = $this->getColumns();
        $search = array_search($column, $columns, true);
        return ($search > -1);
    }

    public function getColumns(): array {
        return $this->getConnection()
                    ->getSchemaBuilder()
                    ->getColumnListing($this->table);
    }

    public function setFillables($data): void {
        $fillables = $this->getFillable();
        foreach ($fillables as $fillable) {
            if (!array_key_exists($fillable, $data) || $data[$fillable] === '') continue;
            $col_data = $data[$fillable];
            $this->setProperty($fillable, $col_data);
        }
    }

    public static function clean(array $data, $model, bool $appendId = true): array {
        $object = new $model();
        $fillables = $object->getFillable();

        foreach ($data as $key => $value) {
            if (($appendId && 'id' === $key) || array_search($key, $fillables, true) !== false) {
                continue;
            }
            unset($data[$key]);
        }
        return $data;
    }

    public function setProperty(string $name, $value): void {
        $method = 'set' . ucfirst($name) . 'Attribute';
        if (method_exists($this, $method)) {
            $this->{$method}($value);
        } else {
            $this[$name] = $value;
        }
    }

    public static function hydrate(array $data, $connection = null) {
        // get calling class so we can hydrate using that type
        $klass = get_called_class();

        // psuedo hydrate
        $collection = new Collection();
        foreach ($data as $raw_obj) {
            $model = new $klass;
            $model = $model->newFromBuilder($raw_obj);
            if (!is_null($connection)) $model = $model->setConnection($connection);
            $collection->add($model);
        }
        return $collection;

    }

    public static function getTableName() {
        $model = with(new static);
        return $model->getConnection()->getDatabaseName().'.'.$model->getTable();
    }

    public function scopeTrashed($query, string $option = 'without') {
        if(empty($option) || ($option !== 'with' && $option !== 'only')) {
            $option = 'without';
        }

        return $query->{$option . "Trashed"}();
    }
}
