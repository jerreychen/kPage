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
use \KuaKee\Response;
use \KuaKee\Common\Headers;

class Controller
{
    protected $viewName = '';

    public function __construct()
    {
        
    }

    public function setViewName($viewName)
    {
        $this->viewName = $viewName;
    }

    protected function redirect($url)
    {
        Response::getInstance()->redirect($url);
    }
    
    protected function post($key = null, $defaultValue = '')
    {
        return Request::getInstance()->post($key, $defaultValue);
    }

    protected function get($key = null, $defaultValue = '')
    {
        return Request::getInstance()->get($key, $defaultValue);
    }

    protected function header($name, $value = null)
    {
        if(empty($value)) {
            return Headers::getInstance()->get($name);
        }

        Headers::getInstance()->add($name, $value);
    }

}