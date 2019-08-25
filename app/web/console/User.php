<?php

namespace app\web\console;

class User {
    public function index() {
        echo 'cmd start! params: '.json_encode(SDF()->consoleParam()).PHP_EOL;
    }
}
