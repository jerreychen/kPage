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

/**
 * 继承该类的视为插件
 */
abstract class Base
{
    // 覆写该属性设置为true，代表插件生效
    protected $registered = false;

    /**
     * 插件的主入口
     */
    abstract function main();

    protected function registerHook($hookName, $callback)
    {
        Manager::registerHook($this, $hookName, $callback);
    }

    protected function registerHooks($hooks) 
    {
        Manager::registerHooks($this, $hooks);
    }
}