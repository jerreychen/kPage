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

use \KuaKee\Route\Router;
use \KuaKee\Route\Dispatcher;
use \KuaKee\Widget\Manager as WidgetManager;

final class App
{
    const VERSION = '3.0.1';
    
    static private $_instance = null;
    static public $rootPath = null;

    private function __construct($appPath)
    {
        self::$rootPath = $appPath;

        include_once __DIR__ . '/Helper.php';

        // 注册自动加载函数
        @spl_autoload_register(array($this, '__autoload_class'));

        // 全局异常捕捉
        @set_exception_handler(array("\KuaKee\Exception", '__handle_exception'));
        
        Config::createInstance($appPath . '/config.php');

        $this->includeHelperFile();
 
        $this->routePage();
    }

    /**
     * 自动加载类
     * @param String $className 类名
     */
    private function __autoload_class($className)
    {
        $filePath = $this->getPath($className);

        // 文件是否存在？
        if(!is_file($filePath)) {
            throw new \Exception('【请求终止】类: ' . $className . '（' . $filePath . '） 不存在!');
        }
        
        require_once( $filePath );
    }

    private function includeHelperFile()
    {
        $helperFile = self::$rootPath . Config::get('helper_file', '/helper.php');
        if(\is_file($helperFile)) {
            include_once $helperFile;
        }
    }
 
    private function routePage()
    {
        if(Config::get('route.enable', false)) {
            $queryKey = Config::get('route.query_key', 's');
            $currentPath = '/';

            if(array_key_exists($queryKey, $_REQUEST)) {
                $currentPath = $_REQUEST[$queryKey];
            }

            $router = Router::createInstance($currentPath);
            $router->dispatch();

            return;
        }

        // 如果没有开启 route，按默认方式执行
        $module = $_GET[Config::get('route.query_module', 'm')] ?? Config::get('default_module', '');
        $controller = $_GET[Config::get('route.query_controller', 'c')] ?? Config::get('default_controller', 'Index');
        $action = $_GET[Config::get('route.query_action', 'a')] ?? Config::get('default_action', 'index');

        // 执行页面方法
        $dispatch = new Dispatcher($module, $controller);
        $dispatch->route($action);
    }

    /**
     * 获取路径
     * @param String $className
     * @return String 文件路径
     */
    private function getPath($className)
    {
        $fileExt = '.php';
        
        $arrClassNameSeg= explode('\\', $className);

        $firstSeg = array_shift($arrClassNameSeg);

        // 如果第一段等于当前的 namespace，那么表示该类是类库中的类
        if($firstSeg === __NAMESPACE__) {
            return  __DIR__ . '/' . implode('/', $arrClassNameSeg) . $fileExt;
        }

        // 如果设置了 namespace
        if($firstSeg === Config::get('namespace', '')) {
            return self::$rootPath . '/' . implode('/', $arrClassNameSeg) . $fileExt;
        }

        return self::$rootPath . '/' . $firstSeg . '/' . implode('/', $arrClassNameSeg) . $fileExt;
    }

    /**
     * 程序开始运行
     * @param String $appDir application 目录
     * @return \KuaKee\App 返回当前 app
     */
    static public function run($appPath)
    {
        if(!self::$_instance) {
            self::$_instance = new self($appPath);
        }

        return self::$_instance;
    }

    static public function getInstance()
    {
        if(!self::$_instance) {
            return null;
        }

        return self::$_instance;
    }
}