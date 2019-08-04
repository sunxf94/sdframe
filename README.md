# simple development framework
SDFrame是一款简单易用、易扩展、高性能的轻量级单文件单入口php框架。

## 快速开始
### web服务器配置
以nginx为例，推荐nginx部分配置举例
```
server {
    listen       80;
    server_name  localhost;
    root   /Users/snowin/git/sdframe/public;
    index  index.html index.htm index.php;
    charset utf-8;

    location / {
        if (!-e $request_filename) {
            rewrite ^/(.*)$ /index.php/$1 last;
            break;
        }
    }

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    #
    location ~ .*\.php($|/) {
       fastcgi_pass   127.0.0.1:9000;
       fastcgi_index  index.php;

       include                          fastcgi_params;
       fastcgi_split_path_info          ^(.+\.php)(/.+)$;
       fastcgi_param    PATH_INFO       $fastcgi_path_info;
       fastcgi_param    PATH_TRANSLATED $document_root$fastcgi_path_info;
       fastcgi_param    SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

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

## SDFrame methods
SDFrame秉承简洁易用的原则，暴露尽量少的方法。全部接口如下表所示

|方法名|方法类型|方法参数|方法作用|备注|
|:---|:---|:---|:---|:---|
|instance|静态||获取应用实例||
|run|非静态||启动方法||
|setTemplate|非静态|viewPath: 模版路径, layoutPath: 布局模版路径|相对路径, 起点不需要路径分隔符|
|assign|非静态|key: 变量名称, value: 变量值|设置模版变量



## 命名空间必须与相对路径一致
框架会根据命名空间来加载文件，因此我们可以自由的组织文件结构，不受框架限制
