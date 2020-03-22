<?php

namespace app\web\controller;

class Base {

    protected $ERROR_SUCCESS = 0;
    protected $ERROR_FAIL = 1;

    public function setTemplate($templateDir, $layoutDir = '') {
        SDF()->setTemplate($templateDir, $layoutDir);
    }

    public function after($data, $e) {
        $resp = [
            'errorNo' => $e->getCode(),
            'errorMsg' => $e->getMessage(),
            'data' => $data,
        ];

        return json_encode($resp, JSON_UNESCAPED_UNICODE);
    }
}
