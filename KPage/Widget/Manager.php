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

namespace KuaKee\Widget;

use \KuaKee\App;
use \KuaKee\Config;

final class Manager
{
    static private $_instance = null;
    private $_widgets = null;

    private function __construct()
    {
        // 初始化
        $this->_widgets = [];

        $this->scanWidgets();
    }
    
    static public function getInstance()
    {
        if(!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 注册钩子
     *
     * @param Base $widget
     * @param String $hookName
     * @param Function $hookCallback
     * @return void
     */
    static public function registerHook($widgetClass, $hookName, $hookCallback)
    { 
        if(! $widgetClass instanceof Base) {
            throw new \Exception('Hook 必须添加给 Base 类的子类对象！');
        }
 
        $widgetName = get_class($widgetClass);
        self::$_instance->_widgets[$widgetName][$hookName] = $hookCallback;
    }
    static public function registerHooks($widgetClass, $hooks) 
    {
        if(! is_array($hooks)) {
            throw new \Exception('Hooks 必须是数组！');
        }
 
        foreach($hooks as $hookName => $hookCallback) {
            self::registerHook($widgetClass, $hookName, $hookCallback);
        }
    }

    private function scanWidgets()
    {
        // 如果插件未启用，不执行
        if(!Config::get('widget.enable', false)) {
            return;
        }

        // 获取插件目录
        $widgetDir = App::$rootPath . Config::get('widget.dir', '/widgets');

        // 根据 widget.list 注册插件
        $widgetList = Config::get('widget.list', []);

        // 如果 $widgetList 不存在 或者 false
        if(!$widgetList) {
            return;
        }
        
        // 如果 widget.list 是字符串数组类型
        if(\is_array($widgetList)) {
            foreach($widgetList as $widgetName) {
                $widgetPath = $widgetDir . '/' . $widgetName;
                if(is_dir($widgetPath)) { 
                    $this->setWidget($widgetName, $widgetPath . '/' . $widgetName . '.php');
                }
            }
            return;
        }
        
        // 扫描插件目录
        foreach(array_diff(\scandir($widgetDir), ['.', '..'])  as $wdName) {
            $widgetPath = $widgetDir . '/' . $wdName;
            if(is_dir($widgetPath)) { 
                $this->setWidget($wdName, $widgetPath . '/' . $wdName . '.php');
            }
        }
    }
 
    private function setWidget($widgetName, $widgetClassFile)
    {
        $this->_widgets[$widgetName]['file'] = $widgetClassFile;
    }
 
    public function triggerByHook($hookName)
    {
        foreach($this->_widgets as $widgetName => $widgetConfig) { 
            $this->trigger($widgetName, $hookName);
        }
    }

    public function trigger($widgetName, $hookName)
    {
        $widgetFile = $this->_widgets[$widgetName]['file'];
        // 插件文件不存在
        if(!is_file($widgetFile)) {
            return;
        }
        
        include_once $widgetFile;

        $widgetRef = new \ReflectionClass($widgetName);
        $widget = $widgetRef->newInstance();
        // 如果不是 Base 的继承类，说明不是 widget，不执行
        if(!$widget instanceof Base) {
            return;
        }
        $registeredProperty = $widgetRef->getProperty('registered');
        $registeredProperty->setAccessible(true);

        // 未启用插件，不执行
        if(!$registeredProperty->getValue($widget)) {
            return;
        }

        $main = $widgetRef->getMethod('main');

        // 是否该方法可以被调用，如果不是 public，报错
        if(!$main->isPublic()) { 
            throw new \Exception('插件的入口方法 main() 不存在或者不可访问！'); 
        }

        call_user_func_array(array($widget, 'main'), []);

        $widgetFilterCallback = $this->_widgets[$widgetName][$hookName];
        // 如果没有该 filter 方法，不执行
        if(!$widgetFilterCallback) {
            return;
        }

        if(\is_callable($widgetFilterCallback)) {
            $widgetFilterCallback();
            return;
        }
        
        \call_user_func_array(array($widget, $widgetFilterCallback), []);
    }
}