<?php

namespace app\web\controller;

use services\TalkService;

class Index extends Base {

    public function index() {
        $talkList = TalkService::getList();

        SDF()->assign('list', $talkList);
        $this->setTemplate("index/index");
    }

    public function test() {

        $param = SDF()->getParam();
        $config = SDF()->getConfig();

        $result = 'SDFrame Start! get param: '.json_encode($param);

        return $result;
    }

    public function before() {
    }
}
