<?php
/**
 * +-----------------------------------------------------------------------
 * | KPage，是温州市夸克网络科技有限公司内部使用的一套简单的php CMS系统框架。
 * +-----------------------------------------------------------------------
 * | 我们的宗旨是：用最少的代码，做更多的事情！
 * +-----------------------------------------------------------------------
 * | 应用程序入口
 * +-----------------------------------------------------------------------
 * @author          Jerrey
 * @license         MIT (https://mit-license.org)
 * @copyright       Copyright © 2021 KuaKee Team. (https://www.kuakee.com)
 * @version         v3
 */

declare (strict_types = 1);

use \KuaKee\App;
use \KuaKee\Config;
use \KuaKee\Request;
use \KuaKee\Exception;

if (!function_exists('lib_root')) {
    /**
     * 取得 库 的路径
     */
    function lib_root()
    {
        return __DIR__;
    }
}

if (!function_exists('app_root')) {
    /**
     * 取得 app 的根路径
     */
    function app_root()
    {
        return App::$rootPath;
    }
}

if (!function_exists('www_url')) {

    /**
     * 返回基于当前域名的url地址
     * @param String $uri
     */
    function www_url($uri = '')
    {
        if(stripos($ui, 'http') === 0) {
            return $uri;
        }

        if(stripos($uri, '/') === 0) {
            $uri = substr($uri, 1);
        }
        return Request::getInstance()->host() . $uri;
    }
}

if (!function_exists('get')) {
    /**
     * 取到 get 值
     */
    function get($key, $defaultValue = null)
    {
        return Request::getInstance()->get($key, $defaultValue);
    }
}

if (!function_exists('post')) {
    /**
     * 取到 post 值
     */
    function post($key, $defaultValue = null)
    {
        return Request::getInstance()->post($key, $defaultValue);
    }
}


if (!function_exists('config')) {
    /**
     * 获取 config 的便捷方法
     */
    function config($key, $defaultValue = '')
    {
        return Config::get($key, $defaultValue);
    }
}

if (!function_exists('is_debug')) {

    /**
     * 是否debug模式
     */
    function is_debug()
    {
        return Config::get('debug', true);
    }
}

if (!function_exists('get_last_exception')) {

    /**
     * 取到最近的错误信息
     */
    function get_last_exception()
    {
        return Exception::$exception;
    }
}

if (!function_exists('get_version')) {

    /**
     * 获取版本信息
     */
    function get_version()
    {
        return 'KPage ' . App::VERSION;
    }
}

if(!function_exists('substring')) {

    /**
     * 截取字符串长度 $len
     * @param String $str
     * @param Integer $len
     * @return String
     */
    function substring($str, $len)
    {
        if(mb_strlen($str) > $len) {
            return mb_substr($str, 0, $len) . '...';
        }

        return $str;
    }
}