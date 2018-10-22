<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 2/16/18
 * Time: 05:43
 */

namespace Larangular\RoutingController;

use Illuminate\Http\Request;

trait RoutingRequests {

    abstract public function allowedMethods();

    abstract public function entries($where = []);

    abstract public function entry($id);

    abstract public function save($data);

    abstract public function delete($id);

    public function index(Request $request) {
        $this->prepareRequest(__FUNCTION__);
        return $this->entries($request->all());
    }

    public function show($id) {
        $this->prepareRequest(__FUNCTION__);
        return $this->entry($id);
    }

    public function create() { }

    public function store(Request $request) {
        $this->prepareRequest(__FUNCTION__);
        return $this->save($request->all());
    }

    public function edit($id) { }

    public function update(Request $request, $id) {
        $this->prepareRequest(__FUNCTION__);

        $data = $request->all();
        $data['id'] = $id;
        return $this->save($data);
    }

    public function destroy($id) {
        $this->prepareRequest(__FUNCTION__);
        return $this->delete($id);
    }

    protected function prepareRequest($method) {
        if(!$this->isRequestAllowed($method)) {
            $this->abortRequest();
        }
    }

    private function isRequestAllowed($method) {
        return \in_array($method, $this->allowedMethods());
    }

    private function abortRequest() {
        abort(403, 'Unauthorized');
    }
}
