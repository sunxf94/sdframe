<?php

namespace libs;

class Logger
{
    const ERR    = 'ERR';       // 一般错误: 一般性错误
    const WARN   = 'WARN';      // 警告性错误: 需要发出警告的错误
    const INFO   = 'INFO';      // 信息: 程序输出信息
    const DEBUG  = 'DEBUG';     // 调试: 调试信息
    const SQL    = 'SQL';       // SQL：SQL语句 注意只在调试模式开启时有效
    const ADMIN  = 'ADMIN';     // ADMIN:后台调用时写入的log

    // 日志记录方式
    const SYSTEM = 0;
    const MAIL   = 1;
    const TCP    = 2;
    const FILE   = 3;

    const LOG_DIR = "logs";

    /**
     * 日期格式
     */
    private static $format = '[Y-m-d H:i:s]';

    /**
     * 全局唯一日志ID
     */
    private static $logId = '';

    /**
     * 记录调试信息
     *
     * @param mixed $message 日志信息
     * @param $message
     * @return void
     */
    public static function debug($message)
    {
        self::wLog($message, self::DEBUG);
    }

    /**
     * 记录一般信息
     */
    public static function info($message)
    {
        self::wLog($message, self::INFO);
    }

    /**
     * 记录警告信息
     *
     * @param mixed $message 日志信息
     * @return void
     */
    public static function warn($message)
    {
        self::wLog($message, self::WARN);
    }

    /**
     * 记录错误信息
     *
     * @param mixed $message 日志信息
     * @return void
     */
    public static function error($message)
    {
        self::wLog($message, self::ERR);
    }

    /**
     * 记录sql信息
     *
     * @param mixed $message 日志信息
     * @return void
     */
    public static function sql($message)
    {
        self::wLog($message, self::SQL);
    }

    /**
     * 记录admin操作信息
     *
     * @param mixed $message 日志信息
     * @return void
     */
    public static function admin($message)
    {
        self::wLog($message, self::ADMIN);
    }


    /**
     * 写日志，对日志内容格式进行简单处理
     *
     * @param mixed $message 日志信息
     * @param string $level  日志级别
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @param string $extra 额外参数
     * @return void
     */
    private static function wLog($message, $level = self::INFO, $type=self::FILE, $destination='', $extra='') {
        $tag = empty($extra['tag']) ? $level : $extra['tag'];
        $backtrace = debug_backtrace();
        $file = isset($backtrace[1]['file']) ? basename($backtrace[1]['file']) : '';
        $line = isset($backtrace[1]['line']) ? $backtrace[1]['line'] : '';

        $_message = self::logInfoFormat($message,$level);
        if($destination==''){
            $_destination= SDF()->getRootPath().DIRECTORY_SEPARATOR.self::LOG_DIR.DIRECTORY_SEPARATOR.self::getLogFileName($message,$level);
        }else{
            $_destination=$destination;
        }
        if (self::mkLogDir()){
            error_log($_message, $type, $_destination,$extra);
        }
    }

    private static function mkLogDir(){
        if (!is_dir(SDF()->getRootPath().DIRECTORY_SEPARATOR.self::LOG_DIR)) {
            if(!mkdir(SDF()->getRootPath().DIRECTORY_SEPARATOR.self::LOG_DIR)) {
                return false;
            }
        }
        return true;
    }
    /**
     * 日志信息保存前格式化处理
     *
     * @param mixed $message 日志信息
     * @param string $level  日志级别
     * @return string
     */
    private static function logInfoFormat($message,$level=self::INFO){
        $message = str_replace("\n", "", print_r($message, true) );
        $now = date(self::$format);
        $logId = self::getLogId();

        return "{$now} [{$logId}] [{$level}] {$message}\n";
    }

    /**
     * 获取保存日志的文件名
     *
     * @param string $level  日志级别
     * @return string fileName
     */
    private static  function getLogFileName($message, $level=self::INFO){
        return strtolower($level).'_'.date('y_m_d').'.log';
    }

    public static function getLogId()
    {
        if (self::$logId === '') {
            self::$logId = sprintf('%x', (intval(microtime(true) * 10000) % 864000000) * 10000 + mt_rand(0, 9999));
        }

        return self::$logId;
    }

}
