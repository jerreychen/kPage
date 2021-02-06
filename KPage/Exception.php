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

use \KuaKee\App;
use \KuaKee\Config;

final class Exception extends \Exception
{
    static public $exception;
 
    static public function __handle_exception($ex)
    {
        // 清除缓冲区
        if(Config::get('exception_only', true)) {
            ob_clean();
            ob_end_flush();
        }
        
        self::$exception = $ex;

        include_once Config::get('exception_page'); 

        exit(); //程序结束
    }
}