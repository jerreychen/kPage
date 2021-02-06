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

namespace KuaKee\Route;

use \KuaKee\App;
use \KuaKee\Config;
use \KuaKee\Request;
use \KuaKee\Response;
use \KuaKee\Exception;

final class Router
{
    static private $_instance = null; 
    static private $_router = null;
    private $_current_path = null;
 
    private function __construct($currentPath)
    {
        $this->_current_path = $currentPath;

        $routerFile = App::$rootPath . '/' . Config::get('route.router', 'router.php');
        if(!is_file($routerFile)) {
            throw new Exception('您开启了route，但是没有找到router文件！');
        }

        self::$_router = include_once $routerFile;
    }

    /**
     * 创建Router实例
     * @return \KuaKee\Router 返回当前实例
     */
    static public function createInstance($currentPath)
    {
        if(!self::$_instance) {
            self::$_instance = new self($currentPath);
        }

        return self::$_instance;
    }

    /**
     * 执行 url 分发
     */
    public function dispatch()
    {
        // 遍历router
        foreach(self::$_router as $key => $value) {
            // 如果是正则表达式 并且匹配到了路由
            if(preg_match('/^\/.*?\/$/', $key, $keyMatches) && preg_match($key, $this->_current_path, $matches)) {
                $this->runPage($value, $matches);
                return true; // 结束遍历
            }
            elseif ($key === $this->_current_path) {
                $this->runPage($value, [$this->_current_path]);
                return true; // 结束遍历
            }
        }

        // 是默认首页
        if($this->_current_path === '/') {
            $this->runPage([ 
                'controller' => Config::get('default_controller', 'Index'), 
                'action' => Config::get('default_action', 'index') 
            ], [$this->_current_path]);
            return true;
        }
 
        throw new Exception('路由寻找失败！');
    }
 
    /**
     * 执行页面逻辑
     * @param Mixed $page 可以是 callable 的 function，也可以是配置
     * @param Array $matches 路由匹配出来的结果
     */
    private function runPage($page, $matches = null)
    {
        if(is_callable($page)) {
            // 根据 key 数组，value 数组进行合并
            call_user_func_array($page, array(Request::getInstance(), Response::getInstance(), $matches)); 
            return;
        }

        $moduleName = $page['module'] ?? Config::get('default_module', '');
        $controllerName = $page['controller'] ?? Config::get('default_controller', 'Index');
        $actionName = $page['action'] ?? Config::get('default_action', 'index');

        $args = $page['args'] ?? [];

        $rgx = '/\\\\(\d+)/';
        if(preg_match($rgx, $moduleName, $moduleMatches)) {
            $moduleName = \preg_replace($rgx, $matches[$moduleMatches[1]] ?? '', $moduleName);
        }
        
        if(preg_match($rgx, $controllerName, $ctlMatches)) {
            $ctrlName = ucfirst( $matches[$ctlMatches[1]] ?? '');   // isset? 
            $ctrlName = $ctrlName ?: Config::get('default_controller', 'Index');    // isempty?
            $controllerName = \preg_replace($rgx, $ctrlName, $controllerName);
        }

        if(preg_match($rgx, $actionName, $actionMatches)) {
            $actName = $matches[$actionMatches[1]] ?? '';   // isset? 
            $actName = $actName ?: Config::get('default_action', 'index');  // isempty?
            $actionName =  \preg_replace($rgx, $actName, $actionName);
        }

        // 如果有$args 并且 $args 内含有正则替换
        if(\is_array($args) && count($args) > 0) {
            // 将 $args 数组中的元素含有正则替换的进行替换
            array_walk($args, function(&$v, $k) use ($rgx, $matches) {
                if(preg_match($rgx, $v, $argMatches)) { 
                    $v = \preg_replace($rgx, $matches[$argMatches[1]] ?? '', $v);
                }
            });
        }
        
        // 如果没有module，则只有一个module：根目录
        $dispatch = new Dispatcher($moduleName, $controllerName);
        $dispatch->route($actionName, $args);
    }
}