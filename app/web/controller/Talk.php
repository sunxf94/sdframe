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
            '麻辣拌',
            '盒饭',
            '酱骨饭',
            '轻食',
            '冷面',
            '鸡公煲',
            '沙县小吃',
            '真功夫',
            '炸鸡',
            '一只蟹蟹煲饭',
            '披萨',
            '烤肉拌饭',
            '烧腊饭',
            '出去吃',
            '老公给订',
            '馄饨',
            '喝粥',
        ];
        $index = rand(0, count($foods) - 1);

        return $foods[$index];
    }
}
