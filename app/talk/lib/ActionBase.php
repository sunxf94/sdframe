<?php

class Lib_ActionBase {

    protected $params = [];

    public function __construct() {
        $this->params = SDF()->requestParams();
    }

    public function call() {
        $data = [];
        try {
            if ($this->init()) {
                $this->before();
                $data = $this->execute();
                $this->after();
            }
        } catch (Exception $ex) {
            // TODO
            throw $ex;
        }

        $resp = ['data' => $data];
        return json_encode($resp, JSON_UNESCAPED_UNICODE);
    }

    protected function init() {
        return true;
    }

    protected function before() {
        return true;
    }

    protected function after() {
        return true;
    }
}
