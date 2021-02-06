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

final class Request
{ 
    static private $_instance;

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

    /**
     * 判断是否 post 方式提交
     */
    public function isPost() 
    {
        return isset($_SERVER['REQUEST_METHOD']) && !strcasecmp($_SERVER['REQUEST_METHOD'],'POST');
    }

    /**
     * 判断是否 Ajax 请求
     */
    public function isAjax()
    {
        if(!(isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
            return false;
        }

        return true;
    }

    /**
     * 判断是否 https 请求
     */
    public function isHttps($url = null)
    {
        if(is_null($url)) {
            return $_SERVER['REQUEST_SCHEME'] === 'https';
        }

        return substr(strtolower($url), 0, 8) == "https://";
    }

    /**
     * 判断是否微信内的请求
     */
    public function isWechat()
    {
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
        }

        return false;
    }

    /**
     * 判断是否支付宝内的请求
     */
    public function isAlipay()
    {
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient') !== false ) {
            return true;
        }

        return false;
    }

    public function scheme()
    {
        return $_SERVER['REQUEST_SCHEME'];
    }

    public function domain()
    {
        return $_SERVER['HTTP_HOST'];
    }
 
    public function host()
    {
        return $this->scheme() .'://'. $this->domain() . '/';
    }
    
    /**
     * 获取当前uri地址
     * @param Boolean $withQueryString 是否带 querystring，默认：false
     * @return String
     */
    public function uri($withQueryString = false)
    {
        $uri = $_SERVER['REQUEST_URI'];
        if($withQueryString) {
            return $uri;
        }

        $idx = stripos($uri, '?');
        if($idx > 0) {
            return substr($uri, 0, $idx);
        }

        return $uri;
    }

    public function query($key = null)
    {
        $uri = $_SERVER['REQUEST_URI'];
        $queryStr = '';
        $idx = stripos($uri, '?');
        if($idx > 0) {
            $queryStr = substr($uri, $idx + 1);
        }

        if(empty($queryStr)) {
            return [];
        }

        $queryParts = explode('&', $queryStr);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            if(count($item) == 2) {
                $params[$item[0]] = $item[1];
            }
        }

        if(empty($key)) {
            return $params;
        }

        return $params[$key];
    }

    /**
     * 请求类型
     */
    public function method()
    {
        if(isset($_SERVER['REQUEST_METHOD'])) {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        }
        return 'GET';
    }

    /**
     * 获取 POST 提交的内容
     * @param String $key post 字段
     * @param Mixed $defaultValue 默认值，如果 $key 字段不存在取该值
     * @return Mixed
     */
    public function post($key = null, $defaultValue = null) 
    {
        if(is_null($key)) {
            return $_POST;
        }
        
        if(array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }

        return $defaultValue;
    }

    /**
     * 获取 GET 提交的内容
     * @param String $key post 字段
     * @param Mixed $defaultValue 默认值，如果 $key 字段不存在取该值
     * @return Mixed
     */
    public function get($key = null, $defaultValue = null) 
    {
        if(is_null($key)) {
            return $_GET;
        }

        if(array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }

        return $defaultValue;
    }
}