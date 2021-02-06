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

namespace KuaKee\Image;

class Position
{ 
    private $_pos_x, $_pos_y;

    public function __construct($posX, $posY)
    {
        $this->_pos_x = $posX;
        $this->_pos_y = $posY;
    }

    public function X() 
    {
        return $this->_pos_x;
    }

    public function Y()
    {
        return $this->_pos_y;
    }

    public function toArray()
    {
        return [
            'x' => $this->X(),
            'y' => $this->Y()
        ];
    }
}