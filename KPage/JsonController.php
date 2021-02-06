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

class JsonController extends Controller
{
    public function __construct()
    {
        if(!Request::getInstance()->isAjax()) {
            exit(json_encode([
                'code'  => 400,
                'message'   => '只允许通过Ajax请求！'
            ]));
        }
    }

    protected function success($message, $data = null) 
    {
        return [
            'code'      => 200,
            'data'      => $data,
            'message'   => $message
        ];
    }

    protected function fail($code, $message)
    {
        return [
            'code'      => $code,
            'message'   => $message
        ];
    }
}