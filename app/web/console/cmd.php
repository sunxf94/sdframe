<?php

// 加载框架文件
include dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'SDFrame.php';

SDF()->setConfig('config/Config.php')
    ->setConsoleMode(true)
    ->run();

