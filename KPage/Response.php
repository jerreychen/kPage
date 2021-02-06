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

namespace KuaKee;

use \KuaKee\Request;
use \KuaKee\Common\Headers;
use \KuaKee\Common\Cookies;

final class Response
{
    static private $_instance = null;

    private function __construct()
    {
        
    }

    static public function getInstance()
    {
        if(!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function render($module, $controller, $action)
    {
        $view = new View($module, $controller, $action);
        $view->render();
    }

    /**
     * 返回格式化的当前时间
     * @param String $format
     * @return String
     */ 
    public function now($format = null)
    {
        if(!is_null($format) && !is_string($format)) {
            return new \Exception('日期格式错误！');
        }

        if(empty($format)) {
            $format = 'H:i:s Y-m-d';
        }

        return date($format, time());
    }

    /**
     * 跳转
     * @param String $url
     * @param Boolean $isPermanent
     */
    public function redirect($url, $isPermanent = false)
    {
        if($isPermanent){
            header('Location: '. $url, true, 301);
        }
        else {
            header('Location: ' . $url);
        }
    }

    /**
     * 重新加载页面
     */
    public function reload()
    { 
        $this->redirect($_SERVER['REQUEST_URI']);
    }

    /**
     * 服务端跳转到referer页面
     */
    public function goback()
    {
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * 带 refer 的跳转请求，返回地址为当前地址
     * @param String $url 请求的地址
     */
    public function transfer($url)
    {
        $current_url = $_SERVER['REQUEST_URI'];
        $this->redirect($url. '?refer=' .urlencode( $current_url ));
    }

    public function headers()
    {
        return Headers::getInstance();
    }

    public function cookies()
    {
        return Cookies::getInstance();
    }

}