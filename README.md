# Simple Development Framework
SDFrame是一款易上手、易扩展、高性能的轻量级单文件的php框架。

## 特点
- 单文件php开发
- 学习成本低
- 单域名支持多端代码
- 命名空间需要与文件的相对路径一致

## 快速开始
将SDFrame.php文件放在工程的根目录下。

### php版本
php版本 >= 7.0。

### 目录结构
clone或者download代码，推荐目录结构如下：

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
- 配置web服务器（如nginx）指向public/index.php目录（引导文件）。
- app目录下面保存着不同的站点，如wap站、api接口，web站等模块。
- SDFrame.php文件为框架文件，放在系统根目录下。如果需要修改，请关注章节[修改SDFrame.php文件的位置](#sdf_pos)。



### web服务器推荐配置
以nginx为例，推荐nginx部分配置，将请求统一转发到index.php处理。
```
location / {
    try_files $uri $uri/ /index.php?$query_string;  # 没有?$query_string会导致获取不到GET参数
}
```

### 引导文件index.php 内容
在引导文件中加载SDFrame.php 文件并执行核心类的run方法即可启动框架。
```php
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'SDFrame.php';

SDF()->run();   // SDF()为全局方法，方便获取class SDFrame的实例
```
### 控制器中的内容
```php
<?php

// 命名空间要与文件相对应用根目录的路径一致，否则无法自动加载文件
namespace app\web\controller;

class Index {

    public function index() {
        // 直接return即可输出内容到浏览器
        return 'SDFrame Start! get param: '.json_encode($param);
    }
}
```

### 查看结果
打开浏览器，在地址栏访问localhost。

```text
SDFrame Start! get param:[]
```

SDFrame带你飞！

## 路由解析规则
SDFrame使用基于url中path的三段路由解析。例如：

```
localhost/wap/user/login
```

SDFrame会解析出域名后三段路由，依次对应为

```php
$module = 'wap';
$controller = 'user';
$action = 'login';
```

SDFrame会加载app目录中的wap目录下的User.php文件，实例化class User并调用login方法。

注：SDFrame解析时，对路由的大小写不敏感，获取三段路由后一律转为小写；在实例化class时会将类的开头字母转为大写。

## 全局方法
SDFrmae框架包含一个全局方法SDF()，SDF方法用于获取class SDFrame的实例，实际底层调用的class SDFrame的instance方法。

## Class SDFrame methods
SDFrame秉承简洁易用的原则，暴露尽量少的方法。全部接口如下表所示:

|方法名|方法类型|方法参数|方法作用|备注|
|:---|:---|:---|:---|:---|
|instance|静态|sdfRootPath: 系统根目录|获取应用实例|建议使用全局方法SDF()代替,   参数sdfRootPath默认为SDFrame.php文件所在位置|
|run|非静态||启动方法|启动系统解析路由并分发请求，为系统的启动方法，SDFrame中的各种set操作需要在run方法前执行|
|setTemplate|非静态|viewPath: 模版路径, layoutPath: 布局模版路径|相对路径, 不需要路径分隔符开头|当布局文件路径被设置时，SDFrame将优先加载布局文件，请在布局文件的合适位置使用加载，内置变量名为content|
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
框架会根据命名空间来加载文件，所以类的命名空间一定要与文件相对于应用根目录的路径一致，可以参考快速开始中的例子。
因此，我们可以最大限度的自由组织文件结构，这种方式还会最大程度的减少自动加载的IO开销。

## controller不需要任何基类
controller中的类不强迫开发者继承任何基类，同时框架会检查controller中是否存在before和after方法，以便用户添加事前善后的等逻辑。

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

请注意，如果使用setTemplate方法给SDFrame设置了模版目录，那么指定的模版文件内容就会被输出，而返回的数据会被忽略。

## <span id="sdf_pos">修改SDFrame.php文件的位置</span>
SDFrame框架默认SDFrame.php所在目录为网站根目录。如果需要修改SDFrame.php文件所在目录，需在获取SDFrame实例时定义网站根目录的位置，支持相对路径或者绝对路径。

```php
SDF(dirname(__DIR__))->run();
```

## 设置带布局文件的模版文件
通过SDFrame提供的 setTemplate 方法，可以加载需要的模版文件。当此方法被调用的时候，如果设置的目录不为空，SDFrame就忽略路由返回的数据，转而输出模版文件内容。


