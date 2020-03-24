<?php

namespace app\web\controller;

use services\TalkService;
use services\UserService;

class Index extends Base {

    public function index() {
        $talkList = TalkService::getList();
        foreach ($talkList as &$talk) {
            $fromUser = UserService::getUserByID($talk['from_id']);
            $talk['from_name'] = $fromUser['nickname'];
            $toUser = UserService::getUserByID($talk['to_id']);
            $talk['to_name'] = $toUser['nickname'];
        }

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
