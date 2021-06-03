<?php

namespace Larangular\RoutingController;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Larangular\Support\Facades\Instance;

trait RoutingRequests
{

    public function index(Request $request)
    {
        $this->prepareRequest(__FUNCTION__);
        return $this->entries($request->all());
    }

    protected function prepareRequest($method): void
    {
        if (!$this->isRequestAllowed($method)) {
            $this->abortRequest();
        }
    }

    private function isRequestAllowed($method): bool
    {
        return \in_array($method, $this->allowedMethods());
    }

    abstract public function allowedMethods();

    private function abortRequest(): void
    {
        abort(403, 'Unauthorized');
    }

    abstract public function entries($where = []);

    public function show($id)
    {
        $this->prepareRequest(__FUNCTION__);
        return $this->entry($id);
    }

    abstract public function entry($id);

    public function create()
    {
    }

    public function store(Request $request)
    {
        $this->prepareRequest(__FUNCTION__);
        return $this->save($request->all());
    }

    abstract public function save($data);

    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {
        $this->prepareRequest(__FUNCTION__);

        $data = $request->all();
        $data['id'] = $this->getBindId($id);

        $method = $this->getUpdateMethod($data);
        return $this->{$method}($data);
    }

    private function getBindId($unknown)
    {
        if (Instance::instanceOf($unknown, Collection::class)) {
            $unknown = $unknown->first();
        }

        if (Instance::instanceOf($unknown, Arrayable::class)) {
            $unknown = $unknown->toArray();
        }

        if (!\is_numeric($unknown) && array_key_exists('id', $unknown)) {
            $unknown = $unknown['id'];
        }

        return $unknown;
    }

    abstract public function getUpdateMethod(array $params): string;

    public function destroy($id)
    {
        $this->prepareRequest(__FUNCTION__);
        return $this->delete($id);
    }

    abstract public function delete($id);
}
