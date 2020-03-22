<?php

// 加载框架文件
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'SDFrame.php';

SDF()->setConfig('config/Config.php')
    ->set('db', function() {
        $options = SDF()->getConfig('db');
        $conf = [];
        $conf['server'] = $options['host'];
        $conf['username'] = $options['username'];
        $conf['password'] = $options['password'];
        $conf['database_name'] = $options['dbname'];
        $conf['port'] = isset($options['port']) ? $options['port'] : '3306';
        $conf['prefix'] = isset($options['prefix']) ? $options['prefix'] : '';
        $conf['charset'] = isset($options['charset']) ? $options['charset'] : 'utf8mb4';
        $conf['database_type'] = 'mysql';
        $conf['logging'] = true;

        return new \libs\Medoo($conf);
    })
    ->run();

