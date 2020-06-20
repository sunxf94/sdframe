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

    public function randFood() {
        $foods = [
            '麻辣烫',
            '小龙虾',
        ];
        $index = rand(0, count($foods) - 1);

        return $foods[$index];
    }
}
