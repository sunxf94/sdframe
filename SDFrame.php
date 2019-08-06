<?php

!defined('SDF_HOME_PATH') && define('SDF_HOME_PATH', __dir__);
// define('APP_PATH', SDF_HOME_PATH.DIRECTORY_SEPARATOR.'app');


// TODO 增加DI
// 使用App方法获取SDFrame实例 全部方法放在SDFrame中


// TODO 单元测试
// try_files
// 设置 app view目录


/**
 * 获取SDFrame实例
 */
function SDF() {
    return SDFrame::instance();
}

/**
 * DB方法，返回数据库Medoo实例，方便使用db
 *
 * @param array|string $options 如果为string，则是键值
 * @return Medoo
 */
function DB($options = [])
{
    // 作用域为此方法 但是当再次调用此方法时，值并不丢失
    static $_instance = [];

    if (empty($options)) {
        return $_instance;
    }

    if (!is_array($options)) {
        throw new \Exception('options for DB must be an array');
    }

    $defaultFields = ['host', 'username', 'password', 'dbname'];
    foreach ($defaultFields as $field) {
        if (!isset($options[$field])) {
            throw new \Exception("options.{$field} not found!");
        }
    }

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
    $_instance = new \core\lib\Medoo($conf);

    return $_instance;
}

/**
 * 设置或获取配置项
 */
function Config($key = '') {
    static $_config = [];

    if (is_array($key)) {
        $_config = array_merge($_config, $key);
        return;
    }
    $keys = explode('.', $key);

    $keyConfig = $_config;
    foreach($keys as $k) {
        if (!isset($keyConfig[$k])) {
            throw new \Exception("config of {$k} not found!");
        }

        $keyConfig = $keyConfig[$k];
    }

    return $keyConfig;

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

    // TODO 如何获取 以下变量
    private $_module = '';
    private $_controller = '';
    private $_action = '';

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

    const CONTROLLER_NAME = 'controller';
    const MODULES_FOLDER_NAME = 'app';

    const ERROR_CODE_SUCCESS = 0;
    const ERROR_CODE_FAIL = 10000;

    public static function instance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct() {

        $this->_autoload();
        $this->_loadConfig();
        $this->_getRequest();
    }

    public function run() {
        // remove after ? for getting module, controller and action
        $requestURI = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);

        $module = 'web';
        $controller = 'index';
        $action = 'index';
        if ($requestURI) {
            $requestURIArr = explode('/', $requestURI);
            if (!empty($requestURIArr[1])) {
                $module = $requestURIArr[1];
            }
            if (!empty($requestURIArr[2])) {
                $controller = $requestURIArr[2];
            }
            if (!empty($requestURIArr[3])) {
                $action = $requestURIArr[3];
            }
        }

        $this->_module = $module;
        $this->_controller = $controller;
        $this->_action = $action;

        $className = ucfirst($controller);
        $classNameWithNamespace = '\\'.self::MODULES_FOLDER_NAME."\\{$module}\\".self::CONTROLLER_NAME."\\{$className}";
        if (!class_exists($classNameWithNamespace)) {
            $this->_message('class not found, className: '.$className);
        }

        $classInstance = new $classNameWithNamespace();
        if (!method_exists($classInstance, $action)) {
            $this->_message('function not found');
        }

        try {
            // before中遇到异常case 请抛异常或者返回false
            if (method_exists($classInstance, 'before') && $classInstance->before() === false) {
                throw new \Exception('invalid access!');
            }

            $resp = $classInstance->$action();

            method_exists($classInstance, 'after') && $classInstance->after();

            $this->_response($resp);
        } catch (\Exception $e) {
            $errorNo = $e->getCode() ? $e->getCode() : self::ERROR_CODE_FAIL;
            $errorMsg = $e->getMessage() ? $e->getMessage() : '系统错误';

            $this->_response('', $errorNo, $errorMsg);
        }
    }

    public function setDB(array $options) {
        if (empty($options)) {
            throw new \Exception('options is empty');
        }

        DB($options);

        return $this;
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

    final public function assign($key, $value) {
        if (!$key) {
            throw new \Exception('invalid key');
        }

        $this->_vars[$key] = $value;
    }

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

    final public function isPost() {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post' ? true : false;
    }

    final public function isGet() {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'get' ? true : false;
    }

    final public function setConfig($path) {
        if (!$path || !file_exists(SDF_HOME_PATH.DIRECTORY_SEPARATOR.$path)) {
            throw new \Exception('invalid path');
        }
        $this->_config = require(SDF_HOME_PATH.DIRECTORY_SEPARATOR.$path);

        return $this;
    }

    final public function getConfig($key) {
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


    private function _response($data, $errorNo = self::ERROR_CODE_SUCCESS, $errorMsg = 'success') {

        $output = '';
        if ($this->_viewPath) {
            ob_start();

            extract($this->_vars);

            $content = $this->_getViewPath($this->_viewPath);

            if ($this->_layoutPath) {
                $layout = $this->_getViewPath('layout');

                // layout template will use _viewPath, too
                include $layout;
            } else {
                include $content;
            }

            $output = ob_get_clean();
        } else {
            // TODO 可以自定义
            $resp = array(
                'errorNo' => $errorNo,
                'errorMsg' => $errorMsg,
                'data' => $data,
            );

            $output = json_encode($resp, JSON_UNESCAPED_UNICODE);
        }

        echo $output; exit;
    }

    private function _getViewPath($viewPath, $viewFolderName = 'view', $ext = 'html') {

        if (!$viewPath) {
            throw new \Exception('invalid view dir');
        }

        $viewPathArr = [SDF_HOME_PATH, self::MODULES_FOLDER_NAME, $this->_module, $viewFolderName, $viewPath];

        return implode(DIRECTORY_SEPARATOR, $viewPathArr).".{$ext}";
    }

    private function _message($msg) {
        echo '[404 NOT FOUND !] message: '.$msg.PHP_EOL;
        exit;
    }

    private function _loadConfig() {
        $configName = SDF_HOME_PATH.DIRECTORY_SEPARATOR.'config/common.php';
        if (file_exists($configName)) {
            $config = require($configName);

            Config($config);
        }
    }

    private function _autoload() {
        spl_autoload_register(function ($className) {

            $filename = SDF_HOME_PATH.DIRECTORY_SEPARATOR.$className.'.php';
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
    }
}

