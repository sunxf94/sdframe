# simple development framework
SDFrame是一款简单易用、易扩展、高性能的轻量级单文件单入口php框架。

## 特点
- 学习成本低

## 快速开始
将SDFrame.php文件放在工程的根目录下
### php版本
php版本 >= 7.0
### web服务器推荐配置
以nginx为例，推荐nginx部分配置; 注意修改root值为代码所在目录
```
server {
    listen       80;
    server_name  localhost;
    root   /your_code_path/sdframe/public;
    index  index.html index.htm index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
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
- 配置web服务器（如nginx）指向public/index.php目录。
- app目录下面保存着不同的站点，如wap站、api接口，web站等模块
- SDFrame.php文件为框架文件，放在系统根目录下。如果需要修改，请关注[修改SDFrame.php文件的位置](#sdf_pos)一章节

### 引导文件index.php 内容
在引导文件中 _include_ SDFrame.php 文件并执行核心类的 _run_ 方法即可启动框架
```php
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'SDFrame.php';

SDF()->run();   // SDF()为全局方法，方便获取class SDFrame的实例
```
### Index.php 内容
```php
<?php

// 命名空间要与路径一致
namespace app\web\controller;

class Index {

    public function index() {
        return 'SDFrame Start! ';
    }
}
```
### 访问localhost
框架启动
```text
SDFrame Start! get param:[]
```

## 全局方法
SDFrmae框架包含一个全局方法SDF()，SDF方法用于获取class SDFrame的实例，实际底层调用的class SDFrame的单例方法

## Class SDFrame methods
SDFrame秉承简洁易用的原则，暴露尽量少的方法。全部接口如下表所示

|方法名|方法类型|方法参数|方法作用|备注|
|:---|:---|:---|:---|:---|
|instance|静态|sdfRootPath: 系统根目录|获取应用实例|可以使用全局方法SDF()代替, 默认SDFrame.php文件所在位置|
|run|非静态||启动方法||
|setTemplate|非静态|viewPath: 模版路径, layoutPath: 布局模版路径|相对路径, 起点不需要路径分隔符|
|assign|非静态|key: 变量名称, value: 变量值|设置模版变量
|getParam|非静态|key: 变量名字符串|获取$_GET的数据|参数可以为空|
|postParam|非静态|key: 变量名字符串|获取$_POST的数据|参数可以为空|
|inputParam|非静态|key: 变量名字符串|获取输入流php://input的数据|参数可以为空|
|setConfig|非静态|path: 配置文件相对SDFrame.php文件的相对路径|设置配置文件信息|例如: config/Config.php|
|getConfig|非静态|key: 配置文件中的key|获取全部/部分配置文件信息|key支持多级，传入“.”分割的字符串|
|setModuleFolderName|非静态|moduleName: module文件夹的名字|设置模块所在文件夹的名字|默认名字为app|
|setViewFolderName|非静态|viewName: view文件夹的名字|设置模块所在文件夹的名字|默认名字为view，view文件夹一定要在具体的模块下|
|setControllerFolderName|非静态|controller: 控制器所在文件夹名字|设置控制器所在文件夹的名字|默认名字为controller，controller文件夹一定要在具体模块下|
|set|非静态|name: 自定义组件的名字，instance: 自定义组件的实例|将自定义组件放入容器中|组件实例可以使用匿名函数|
|__get|非静态|name: 自定义组件的名字|从容器中获取组件||
|getModuleName|非静态||获取模块名字||
|getConrollerName|非静态||获取控制器名字||
|getActionName|非静态||获取控制器中方法的名字||

## 命名空间必须与相对路径一致
框架会根据命名空间来加载文件，因此我们可以自由的组织文件结构，不受框架限制

## controller不需要任何基类
controller不需要继承任何框架提供的基类（如果需要自己的基类，请随意）。同时框架会检查controller中是否存在before和after方法，以便用户添加事前善后的处理逻辑。

## 输出json串
在controller中function中return即可输出json，json的格式需要自己定义。
例如：

```php
<?php

namespace app\api\controller

class Index {
    public function index() {
        $data = ['count' => 10];
        $res = [
            'errno' => 0,
            'errmsg' => 'success',
            'data' => $data,
        ];

        return json_encode($res);
    }
}
```

## <span id="sdf_pos">修改SDFrame.php文件的位置</span>
SDFrame框架默认SDFrame.php所在目录为网站根目录。如果需要修改SDFrame.php文件所在目录，需在引导文件中定义网站根目录的位置

```php
SDF(dirname(__DIR__))->run();
```
支持相对路径或者绝对路径


