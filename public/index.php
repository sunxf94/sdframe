<?php

// 设置时区 覆盖服务器配置
ini_set('date.timezone','Asia/Shanghai');

// TODO 需要的时候再启动
session_start();

// 加载框架文件
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'SDFrame.php';

SDFrame::instance()->setDB([
    'host' => Config('db.host'),
    'username' => Config('db.username'),
    'password' => Config('db.password'),
    'dbname' => Config('db.dbname'),
    'prefix' => Config('db.prefix'),
])->run();

