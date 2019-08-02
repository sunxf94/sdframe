# simple development framework
SDFrame是一款简单易用、易扩展、高性能的轻量级单文件单入口php框架。

## 快速开始
### 目录结构
```
.
├── SDFrame.php
├── app
│   └── web
│       └── controller
│           └── Index.php
└── public
    └── index.php
```
配置web服务器（如nginx）指向public/index.php目录
### 引导文件index.php 内容
在引导文件中 _include_ SDFrame.php 文件并执行核心类的 _run_ 方法即可启动框架
```php
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'SDFrame.php';

SDFrame::instance()->run();
```
### Index.php 内容
```php
<?php

// 命名空间要与路径一致
namespace app\web\controller;

class Index extends \Controller{

    public function index() {
        return 'SDFrame Start! ';
    }
}
```
### 访问localhost
框架启动
```json
{
  errorNo: 0,
  errorMsg: "success",
  data: "SDFrame Start! "
}
```


## 命名空间必须与相对路径一致
框架会根据命名空间来加载文件，因此我们可以自由的组织文件结构，不受框架限制
