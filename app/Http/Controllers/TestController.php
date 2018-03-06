<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CrudController;
use \Auth;

class TestController extends CrudController
{   
    // Model name
    public $model = 'Test';

    // Table name
    public $table = 'tests';

    public $data = [];

    public $rules = [ 'title' => 'required|min:2|unique:tests|sometimes', 'body'  => 'required|sometimes' ];

    // for index
    public function preList($model) {
        return $model;
    }

    // for store
    public function create($data = null) {
        return $data;
    }

    // for update
    public function edit($id, $data = []) {
        return $data;
    }

    // for destroy
    public function delete($id, $data = []) {
        return $data;
    }
}
