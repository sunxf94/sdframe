<?php

namespace core\lib;

class Controller {

    private $_paramsGet = [];
    private $_paramsPost = [];
    private $_paramsRow = [];

    protected $module = '';
    protected $controller = '';
    protected $action = '';

    public function __construct() {
        $this->_paramsGet = $_GET;
        $this->_paramsPost = $_POST;

        $input = file_get_contents('php://input');
        $this->_paramsRow = json_decode($input, true);
    }

    final public function init(array $request) {
        $this->module = $request['module'];
        $this->controller = $request['controller'];
        $this->action = $request['action'];
    }

    final protected function getParam($key = '', $defaultValue = '') {
        if (!$key) {
            return $this->_paramsGet;
        }

        return isset($this->_paramsGet[$key]) ? $this->_paramsGet[$key] : $defaultValue;
    }

    final protected function postParam($key = '', $defaultValue = '') {
        if (!$key) {
            return $this->_paramsPost;
        }

        return isset($this->_paramsPost[$key]) ? $this->_paramsPost[$key] : $defaultValue;
    }

    final protected function rawParam($key = '', $defaultValue = '') {
        if (!$key) {
            return $this->_paramsRow;
        }

        return isset($this->_paramsRow[$key]) ? $this->_paramsRow[$key] : $defaultValue;
    }

    // TODO 多个模版 如何加载
    final protected function setTemplate($templateDir, $layoutDir = 'layout') {
        \SDFrame::instance()->setTemplate($templateDir, $layoutDir);
    }

    final protected function assign($key, $value) {
        \SDFrame::instance()->assign($key, $value);
    }

    final protected function isPost() {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post' ? true : false;
    }

    final protected function isGet() {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'get' ? true : false;
    }

    public function before() {}
    public function after() {}
}

