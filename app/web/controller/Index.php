<?php

namespace app\web\controller;

class Index {

    public function index() {

        $param = SDF()->getParam();
        $config = SDF()->getConfig();

        $result = 'SDFrame Start! get param: '.json_encode($param);

        return $result;
    }

    public function before() {
    }
}
