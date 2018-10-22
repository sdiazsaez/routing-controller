<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 9/28/18
 * Time: 18:12
 */

namespace Larangular\RoutingController\EnumController;

use Illuminate\Database\Eloquent\Collection;
use Larangular\RoutingController\{
    Controller, Contracts\HasPagination, Contracts\HasResource, Contracts\IGatewayModel, MakeResponse,
    Resource
};
use Larangular\Support\Enum;
use Msd\Sura\Autoclick\Models\EnumType;

abstract class EnumController extends Controller implements IGatewayModel, HasResource {
    use MakeResponse;

    abstract public function enumValues(): array;
    abstract public function onCreateEnum($enumKey, $key, $value): EnumModel;

    public function resource() {
        return Resource::class;
    }

    public function model() {
        // TODO: Implement model() method.

    }

    public function allowedMethods() {
        return [
            //'index',
            'show',
        ];
    }

    public function entries($where = []) {
        if(empty($where)){
            $where['type'] = array_keys($this->enumValues());
        } else {
            $where['type'] = [$where['type']];
        }

        $response = [];
        foreach($where['type'] as $type) {
            $response[] = $this->createEnum($type);
        }

        return $this->makeResponse(new Collection($response));
    }

    public function entry($id) {
        $response = $this->createEnum($id);
        return $this->makeResponse($response);
    }

    private function getEnumClass(string $enumKey): string {
        $values = $this->enumValues();
        return $values[$enumKey];
    }

    private function createEnum(string $enumKey): Collection {
        $constants = Enum::getConstants($this->getEnumClass($enumKey));
        $values = [];
        foreach ($constants as $key => $value) {
            $values[] = $this->onCreateEnum($enumKey, $key, $value);
        }

        return new Collection($values);
    }

}
