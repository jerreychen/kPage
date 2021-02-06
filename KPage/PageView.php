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

final class PageView
{
    private $_view_data;
    private $_view_name;

    public function __construct($viewName = null, $viewData = null)
    {
        $this->_view_name = $viewName;
        $this->_view_data = $viewData ?: [];
    }

    public function __set($name, $value)
    {
        $this->_view_data[$name] = $value;
    }

    public function __get($name)
    {
        return array_key_exists($name, $this->_view_data) ? $this->_view_data[$name] : '';
    }

    public function __isset($name)
    {
        return isset($this->_view_data[$name]);
    }

    public function __unset($name)
    {
        unset($this->_view_data[$name]);
    }

    public function setView($viewName) 
    {
        $this->_view_name = $viewName;
    }

    public function getViewName()
    {
        return $this->_view_name;
    }

    public function getViewData()
    {
        return $this->_view_data;
    }
}