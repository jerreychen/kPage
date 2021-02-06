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

namespace KuaKee\ORM;

final class GroupBy
{
    static private $instance = null;

    private $queryable;

    private function __construct($queryable) 
    {
        $this->$queryable = $queryable;
    }

    static public function create($queryable)
    {
        if(!(self::$instance instanceof self)) {
            self::$instance = new self($queryable);
        }

        return self::$instance;
    }
}