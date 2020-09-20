<?php

class Action_Index_Index extends Lib_ActionBase {

    public function execute() {

        $talkList = Service_Talk::getList();
        foreach ($talkList as &$talk) {
            $fromUser = UserService::getUserByID($talk['from_id']);
            $talk['from_name'] = $fromUser['nickname'];
            $toUser = UserService::getUserByID($talk['to_id']);
            $talk['to_name'] = $toUser['nickname'];
        }

        SDF()->assign('list', $talkList);
        $this->setTemplate("index/index");

        return $this->params;
    }
}
