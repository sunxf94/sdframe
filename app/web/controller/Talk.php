<?php

namespace app\web\controller;

use services\TalkService;

class Talk extends Base {

    public function add() {
        $content = SDF()->postParam('content');
        if (empty($content)) {
            throw new \Exception('text is empty');
        }

        return TalkService::add($content);
    }
}
