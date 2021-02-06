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

namespace KuaKee\Common;

use KuaKee\Config;

final class Cookies
{
    static private $_instance;

    private $_path = '', $_domain = '';

    private function __construct()
    {
        $this->_path = '/';
        $this->_domain = Config::get('cookie_domain', $_SERVER['SERVER_NAME']);;
    }

    static public function getInstance()
    {
        if(!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function create($path, $domain)
    {
        $this->_path = $path;
        $this->_domain = $domain;
    }

    public function get($name)
    {
        if(array_key_exists($name, $_COOKIE)) {
            return $_COOKIE[$name];
        }

        return false;
    }

    public function add($name, $value, $expires = 7200)
    {
        setcookie($name, $value, time() + $expires, $this->_path, $this->_domain);
    }

    public function remove($name)
    {
        setcookie($name, '', time() - 3600, $this->_path, $this->_domain);
    }
}