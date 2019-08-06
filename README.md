# simple development framework
SDFrame是一款简单易用、易扩展、高性能的轻量级单文件单入口php框架。

## 快速开始
### php版本
任何大于7的php版本都可以运行SDFrame
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

## 全局方法
SDFrmae框架包含一个全局方法，SDF()
SDF() 用于获取class SDFrame的实例，实际底层调用的class SDFrame的单例方法

## Class SDFrame methods
SDFrame秉承简洁易用的原则，暴露尽量少的方法。全部接口如下表所示

|方法名|方法类型|方法参数|方法作用|备注|
|:---|:---|:---|:---|:---|
|instance|静态||获取应用实例||
|run|非静态||启动方法||
|setTemplate|非静态|viewPath: 模版路径, layoutPath: 布局模版路径|相对路径, 起点不需要路径分隔符|
|assign|非静态|key: 变量名称, value: 变量值|设置模版变量
|getParam|非静态|key: 变量名字符串|获取$_GET的数据|参数可以为空|
|postParam|非静态|key: 变量名字符串|获取$_POST的数据|参数可以为空|
|inputParam|非静态|key: 变量名字符串|获取输入流php://input的数据|参数可以为空|
|setConfig|非静态|path: 配置文件相对SDFrame.php文件的相对路径|设置配置文件信息|例如: config/Config.php|
|getConfig|非静态|key: 配置文件中的key|获取全部/部分配置文件信息|key支持多级，传入“.”分割的字符串|

## 命名空间必须与相对路径一致
框架会根据命名空间来加载文件，因此我们可以自由的组织文件结构，不受框架限制

## 有问题就抛异常
验参、权限、类型等逻辑遇到非法case时，请尽情的抛异常，框架会帮你捕获

## controller不需要任何基类

## 增加SDF全局方法
