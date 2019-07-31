<?php

namespace app\api\controller;

class Index {

    public function test() {

        $date = date('Y-m-d H:i:s');
        $result = 'SDFrame Start! '.$date;

        return $result;
    }
}
