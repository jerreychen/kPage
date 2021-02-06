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
use \KuaKee\Controller;
use \KuaKee\JsonController;
use \KuaKee\View;
use \KuaKee\PageView;
use \KuaKee\Request;
use \KuaKee\Exception;
use \KuaKee\Common\DocComment;

final class Dispatcher
{
    private $_controller, 
            $_module_name, 
            $_view_relative_path, 
            $_controller_ref;
    
    public function __construct($module, $controller)
    {
        $this->_module_name = $module;
        // 如果 $controller 含有 . 分隔符号
        $this->_view_relative_path = str_replace('.', '/', $controller);

        $controllerClsName = [];
        if(Config::get('namespace')) {
            array_push($controllerClsName, Config::get('namespace'));
        }
        if($module) {
            array_push($controllerClsName, $module);
        }
        array_push($controllerClsName, Config::get('controller_path', 'controller'));
        // 如果 $controller 含有 . 分隔符号
        array_push($controllerClsName, str_replace('.', '\\', $controller));

        $controllerName = implode('\\', $controllerClsName);
        $this->_controller_ref = new \ReflectionClass($controllerName);

         // 判断 controller 是否能被实例化
         if(!$this->_controller_ref->isInstantiable()) {
            throw new Exception('不能实例化的 Controller: ' . $controllerName);
        }

        // 实例化controller
        $this->_controller = $this->_controller_ref->newInstance();
        
        $initFunName = '__init__';
        // 如果该 Controller 集成自 Controller 类，如果存在 __init__ 方法，则执行 __init__ 方法
        if($this->_controller instanceof Controller 
            && $this->_controller_ref->hasMethod($initFunName)) { 
            $this->__invokeAction($initFunName);
        }
    }

    private function __invokeAction($actionName, $args = array())
    {
        // 取出该方法
        $action = $this->_controller_ref->getMethod($actionName);
        
        // 是否该方法可以被调用，如果不是 public，报错
        if(!$action->isPublic()) {
            // 如果方法名是 __ 开头，不报错
            if(stripos($actionName, '__') === 0) {
                return false;
            }

            throw new Exception('Action 方法： '. $actionName .' 不存在！'); 
        }

        $params = array();
        $ps = $action->getParameters();
        
        foreach($ps as $p) {
            $defaultValue = null;
            if($p->isDefaultValueAvailable()) { 
                $defaultValue = $p->getDefaultValue();
            }
            array_push($params, (array_key_exists($p->name, $args)) ? $args[$p->name] : $defaultValue);
        }

        // 调用 Action 方法
        return call_user_func_array(array($this->_controller, $actionName), $params);
    }

    /**
     * 判断是否支持 REQUEST_METHOD
     */
    private function __allowMethod($actionName, $method)
    {
        // 取出该方法
        $action = $this->_controller_ref->getMethod($actionName);

        $doc = $action->getDocComment();
        // 未定义，返回true
        if(!$doc) {
            return true;
        }

        $cmt = new DocComment($doc);
        $allowedMethod = $cmt->getNode('method');
        if(!$allowedMethod) {
            return true;
        } 

        return stripos($allowedMethod, $method);
    }

    /**
     * 调用 action 方法
     */
    public function route($actionName, $args = null)
    {
        // 如果没有该方法，报错
        if(!$this->_controller_ref->hasMethod($actionName)) { 
            throw new Exception('Action 方法：'. $actionName .' 不存在！');
        }

        $invokeResult = $this->__invokeAction($actionName, $args ?? []);
        
        if(!isset($invokeResult)) {
            return;
        }
        
        // 如果是 JsonController 实例
        if($this->_controller instanceof JsonController) {
            if(!$this->__allowMethod($actionName, Request::getInstance()->method())) {
                echo json_encode([ 'code' => 403, 'message' => '不支持该请求方式！']);
                return;
            }

            // 如果是json 设置 Content-Type 类型为 application/json
            header("Content-Type:application/json; charset=utf-8");

            echo json_encode($invokeResult ?? []);
            return;
        }
        
        // 如果是 Controller 实例
        if($this->_controller instanceof Controller) {

            // 读取 viewName 属性，如果 viewname 未设置，默认值为 actionName
            $viewNameProperty = $this->_controller_ref->getProperty('viewName');
            $viewNameProperty->setAccessible(true);
            $viewName = $viewNameProperty->getValue($this->_controller);
            if(empty($viewName)) {
                $viewName = $actionName;
            }

            $viewPage = new View($this->_module_name, $this->_view_relative_path, $viewName);

            if(is_array($invokeResult)) {
                $viewPage->setViewData($invokeResult);
            } elseif ($invokeResult instanceof Pageview) {
                $currentViewName = $invokeResult->getViewName();
                $viewData = $invokeResult->getViewData();
     
                if(!empty($currentViewName) && $viewName != $currentViewName) {
                    $viewPage->setViewName($currentViewName);
                }
                if(is_array($viewData)) {
                    $viewPage->setViewData($viewData);
                }
            }
            
            $preAllFunName = '__pre__';
            // 如果该 Controller 集成自 Controller 类，如果存在 __pre__ 方法，则执行 __pre__ 方法
            if($this->_controller instanceof Controller 
                && $this->_controller_ref->hasMethod($preAllFunName)) {
                $this->__invokeAction($preAllFunName, ['view' => $viewPage]);
            }
    
            $preFunName = '__pre__' . $actionName;
            if($this->_controller instanceof Controller 
                && $this->_controller_ref->hasMethod($preFunName)) {
                $this->__invokeAction($preFunName, ['view' => $viewPage]);
            }
    
            $viewPage->render();

            return;
        }
        
        if($invokeResult instanceof Pageview) {
            $viewName = $invokeResult->getViewName();
            $viewData = $invokeResult->getViewData();

            $viewPage = new View($this->_module_name, $this->_view_relative_path, $viewName);

            if(is_array($viewData)) {
                $viewPage->setViewData($viewData);
            }
            
            $viewPage->render();

            return;
        }
        
    }
}