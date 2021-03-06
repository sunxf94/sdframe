<?php

/**
 * SDFrame为一款轻量级的单文件框架
 * 默认请将SDFrame.php放在应用根目录下，如需放在其他目录，请配置系统根目录
 * SDF($sdfRootPath)->run();
 *
 * @author sunxuefeng sunxf94@gmail.com
 * @github github.com/sunxf94/sdframe
 */

// TODO 单元测试
// 默认的module controller action改为可配置。考虑使用方法还是使用配置文件
// 命令行模式 增加cmd readme composer psr标准
// readme 增加：方便迁移


/**
 * 获取SDFrame实例
 */
function SDF($sdfRootPath = __DIR__) {
    return SDFrame::instance($sdfRootPath);
}

class SDFrame {

    /**
     * 单例
     */
    private static $_instance = '';

    /**
     * 视图相对路径
     */
    private $_viewPath = '';

    /**
     * 布局视图相对路径
     */
    private $_layoutPath = '';

    /**
     * 访问的路由信息
     * www.example.com/admin/user/login
     * www.example.com/{$_module}/{$_controller}/{$_action}
     */
    private $_module = 'web';
    private $_controller = 'index';
    private $_action = 'index';

    /**
     * 请求参数信息
     */
    private $_paramsGet = [];
    private $_paramsWithForm = [];
    private $_paramsWithoutForm = [];

    /**
     * 保存配置文件信息
     */
    private $_config = [];

    /**
     * 保存页面需要的变量
     */
    private $_vars = [];

    /**
     * 应用文件夹信息配置存储
     */
    private $_module_folder_name = 'app';
    private $_view_folder_name = 'view';
    private $_controller_folder_name = 'controller';

    /**
     * 容器 管理开发者自定义组件
     */
    private $_di = [];

    private $_sdf_root_path = __dir__;

    /**
     * 是否启动命令行模式
     * 决定了路由的解析规则 默认不启动
     */
    private $_consoleMode = false;

    /**
     * 单例
     */
    public static function instance($sdfRootPath = __DIR__) {
        if (!self::$_instance) {
            self::$_instance = new self($sdfRootPath);
        }

        return self::$_instance;
    }

    /**
     * @param $sdfRootPath string 系统根目录
     */
    public function __construct($sdfRootPath) {
        $this->_sdf_root_path = $sdfRootPath;

        // 全局异常捕捉
        set_exception_handler(function ($e) {
            $this->_message($e->getMessage());
        });

        $this->_autoload();     // 注册自动加载函数
        $this->_getRequest();   // 获取请求参数备用
        $this->_getRoute();     // 解析路由
    }

    /**
     * 启动应用
     */
    final public function run() {

        $className = ucfirst($this->_controller);
        $classNameWithNamespace = '\\'.$this->_module_folder_name."\\{$this->_module}\\".$this->_controller_folder_name."\\{$className}";
        if (!class_exists($classNameWithNamespace)) {
            $this->_message('class not found, className: '.$classNameWithNamespace);
        }

        $classInstance = new $classNameWithNamespace();
        if (!method_exists($classInstance, $this->_action)) {
            $this->_message('function not found');
        }

        // before中遇到异常case请抛异常
        $resp = '';
        try {
            if (method_exists($classInstance, 'before')) {
                $classInstance->before();
            }

            $resp = $classInstance->{$this->_action}();
            $resp = $classInstance->after($resp, new \Exception('success'));
        } catch (\Exception $e) {
            if (method_exists($classInstance, 'after')) {
                $resp = $classInstance->after($resp, $e);
            }
        }

        $this->_response($resp);
    }

    /**
     * set the path of the view
     * TODO 多个模版 如何加载
     * TODO 默认模版为layout
     *
     * @param $viewPath string
     * @param $layoutPath string (if we need)
     */
    final public function setTemplate($viewPath, $layoutPath = '') {
        if (!$viewPath) {
            throw new \Exception('invalid template dir');
        }
        $this->_viewPath = $viewPath;
        $this->_layoutPath = $layoutPath;
    }

    /**
     * 设置模版变量
     *
     * @param $key string 模版中变量的名字
     * @param $value mix 模版中变量的值
     */
    final public function assign($key, $value) {
        if (!$key) {
            throw new \Exception('invalid key');
        }

        $this->_vars[$key] = $value;
    }

    /**
     * 获取$_GET 中的参数
     */
    final public function getParam($key = '', $defaultValue = '') {
        if (!$key) {
            return $this->_paramsGet;
        }

        return isset($this->_paramsGet[$key]) ? $this->_paramsGet[$key] : $defaultValue;
    }

    /**
     * 获取php中 $_POST包含的数据
     * 包含Content-Type是application/x-www-form-urlencoded或multipart/form-data的数据
     *
     * @doc https://www.php.net/manual/zh/reserved.variables.post.php
     */
    final public function postParam($key = '', $defaultValue = '') {
        if (!$key) {
            return $this->_paramsWithForm;
        }

        return isset($this->_paramsWithForm[$key]) ? $this->_paramsWithForm[$key] : $defaultValue;
    }

    /**
     * 获取php中请求的原始数据流php://input的数据
     * 不包含Content-Type是multipart/form-data的数据
     *
     * @doc https://www.php.net/manual/zh/wrappers.php.php
     */
    final public function inputParam($key = '', $defaultValue = '') {
        if (!$key) {
            return $this->_paramsWithoutForm;
        }

        return isset($this->_paramsWithoutForm[$key]) ? $this->_paramsWithoutForm[$key] : $defaultValue;
    }


    final public function consoleParam($key = '', $defaultValue = '') {
        if (!$key) {
            return $this->_paramsConsole;
        }
        return isset($this->_paramsConsole[$key]) ? $this->_paramsConsole[$key] : $defaultValue;
    }

    /**
     * 判断http method是否是POST
     *
     * @return bool
     */
    final public function isPost() {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post' ? true : false;
    }

    /**
     * 判断http method是否是GET
     *
     * @return bool
     */
    final public function isGet() {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'get' ? true : false;
    }

    /**
     * 设置配置文件
     *
     * @param $path string 配置文件相对于应用根目录的路径
     */
    final public function setConfig($path) {
        if (!$path || !file_exists($this->_sdf_root_path.DIRECTORY_SEPARATOR.$path)) {
            throw new \Exception('invalid path');
        }
        $this->_config = require($this->_sdf_root_path.DIRECTORY_SEPARATOR.$path);

        return $this;
    }

    /**
     * 获取配置文件
     *
     * @param $key string 配置文件的key 支持多级 例如: sdframe.df.master.dbname
     * @return array|mix 当key为空时 返回整个配置
     */
    final public function getConfig($key = '') {
        if (!$key) {
            return $this->_config;
        }
        $keys = explode('.', $key);

        $keyConfig = $this->_config;
        foreach($keys as $k) {
            if (!isset($keyConfig[$k])) {
                throw new \Exception("config of {$k} not found!");
            }

            $keyConfig = $keyConfig[$k];
        }

        return $keyConfig;
    }

    /**
     * 设置模块文件夹的名字
     */
    final public function setModuleFolderName($moduleName) {
        if (!$moduleName) {
            throw new \Exception('invalid module folder name');
        }
        $this->_module_folder_name = $moduleName;

        return $this;
    }

    /**
     * TODO 可以支持第二个参数 支持自定义view文件夹位置
     * 设置模版文件夹的名字
     */
    final public function setViewFolderName($viewName) {
        if (!$viewName) {
            throw new \Exception('invalid view folder name');
        }
        $this->_view_folder_name = $viewName;

        return $this;
    }

    /**
     * 设置控制器文件夹的名字
     */
    final public function setControllerFolderName($controllerName) {
        if (!$controllerName) {
            throw new \Exception('invalid controller folder name');
        }
        $this->_controller_folder_name = $controllerName;

        return $this;
    }

    /**
     * 增加用户自定义组件到容器
     *
     * @param $name string 自定义组件名字
     * @param $instance 组件实例
     */
    final public function set($name, $instance) {
        if (!$name || !$instance) {
            throw new \Exception('invalid di setting');
        }

        if ($instance instanceof Closure) {
            $instance = $instance();
        }

        $this->_di[$name] = $instance;

        return $this;
    }

    /**
     * 通过用户设置的组件名字获取自定义组件
     *
     * @param $name string 自定义组件名字
     */
    final public function __get($name) {
        if (!$name) {
            throw new \Exception('invalid name getting');
        }

        return $this->_di[$name];
    }

    /**
     * 获取请求url中模块的名字
     */
    final public function getModuleName() {
        return $this->_module;
    }

    /**
     * 获取请求url中控制器的名字
     */
    final public function getControllerName() {
        return $this->_controller;
    }

    /**
     * 获取请求url中方法的名字
     */
    final public function getActionName() {
        return $this->_action;
    }

    final public function setConsoleMode($setter) {
        $this->_consoleMode = $setter;

        return $this;
    }

    final public function getRootPath() {
        return $this->_sdf_root_path;
    }

    private function _response($output) {

        if ($this->_viewPath) {
            ob_start();

            extract($this->_vars);

            $content = $this->_getViewPath($this->_viewPath);

            if ($this->_layoutPath) {
                $layout = $this->_getViewPath($this->_layoutPath);

                // layout template will use _viewPath, too
                include $layout;
            } else {
                include $content;
            }

            $output = ob_get_clean();
        }

        echo $output; exit;
    }

    private function _getViewPath($viewPath, $ext = 'html') {

        if (!$viewPath) {
            throw new \Exception('invalid view dir');
        }

        $viewPathArr = [$this->_sdf_root_path, $this->_module_folder_name, $this->_module, $this->_view_folder_name, $viewPath];

        return implode(DIRECTORY_SEPARATOR, $viewPathArr).".{$ext}";
    }

    private function _message($msg) {
        echo '[404 NOT FOUND !] message: '.$msg.PHP_EOL;
        exit;
    }

    private function _autoload() {
        spl_autoload_register(function ($className) {

            $filename = $this->_sdf_root_path.DIRECTORY_SEPARATOR.$className.'.php';
            $filename = str_replace('\\', '/', $filename);
            if (file_exists($filename)) {
                require_once $filename;
            }

            return;
        });
    }

    private function _getRequest() {
        $this->_paramsGet = $_GET;
        $this->_paramsWithForm = $_POST;

        $input = file_get_contents('php://input');
        $this->_paramsWithoutForm = json_decode($input, true);
        if ($this->_consoleMode) {
            // $_SERVER['argv'] 第一位固定是脚本名字 之后的三位对应 module controller action
            // 所以从第5位开始
            $this->_paramsConsole = array_slice($_SERVER['argv'], 4);
        }
    }

    private function _getRoute() {
        if ($this->_consoleMode) {
            // TODO 控制台程序的文件夹名字可修改
            $this->setControllerFolderName('console');
            $params = $_SERVER['argv'];
            if (empty($params[1])) {
                throw new \Exception('module is invalid');
            }
            if (empty($params[2])) {
                throw new \Exception('controller is invalid');
            }
            if (empty($params[3])) {
                throw new \Exception('action is invalid');
            }
            $this->_module = $params[1];
            $this->_controller = $params[2];
            $this->_action = $params[3];

            return;
        }
        // remove all after ? for getting module, controller and action
        // 去掉问号后的参数 保证获取到准确的module、controller 和 action
        $requestURI = preg_replace('|\?.*$|', '', $_SERVER['REQUEST_URI']);

        if ($requestURI) {
            $requestURIArr = explode('/', $requestURI);

            // requestURI 的第一个字符一定是/，explode后第一个元素一定是空
            if (!empty($requestURIArr[1])) {
                $this->_module = strtolower($requestURIArr[1]);
            }
            if (!empty($requestURIArr[2])) {
                $this->_controller = strtolower($requestURIArr[2]);
            }
            if (!empty($requestURIArr[3])) {
                $this->_action = strtolower($requestURIArr[3]);
            }
        }
    }
}

