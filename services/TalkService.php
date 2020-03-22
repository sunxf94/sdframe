<?php

namespace services;

use libs\Medoo;
use libs\Logger;

class TalkService {
    public static function add($content) {
        if (empty($content)) {
            throw new \Exception('text is empty');
        }

        $time = time();
        $talk = [
            'from_id' => 1,
            'to_id' => 2,
            'content' => $content,
            'ct_time' => $time,
            'up_time' => $time,
            'status' => 1,
        ];
        $insertID = SDF()->db->insert('talks', $talk);
        if (empty($insertID)) {
            $errorInfo = SDF()->db->error();
            Logger::error(json_encode($errorInfo, JSON_UNESCAPED_UNICODE));
            throw new \Exception('error to insert');
        }

        return $insertID;
    }

    public static function getList() {
        $where = 'status = 1';
        $list = SDF()->db->select('talks', '*', $where);
        if (!$list) {
            $errorInfo = SDF()->db->error();
            Logger::error(json_encode($errorInfo, JSON_UNESCAPED_UNICODE));
            $list = [];
        }

        return $list;
    }
}
