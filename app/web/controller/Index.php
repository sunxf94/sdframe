<?php

namespace app\web\controller;

class Index {

    public function index() {

        $param = SDF()->getParam();

        $result = 'SDFrame Start! ';

        return $result;
    }

    public function before() {
    }
}
