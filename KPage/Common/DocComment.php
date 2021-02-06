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

namespace KuaKee\Common;

final class DocComment
{ 
    private $_comment;

    public function __construct($comment)
    {
        $this->_comment = $comment;
    }

    public function getNode($name)
    {
        if(preg_match('/@('.$name.').*/', $this->_comment, $matches)) {
            return $matches[0];
        }

        return false;
    }
}