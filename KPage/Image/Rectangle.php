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

class Rectangle extends Position
{
    private $_width, $_height;

    public function __construct($posX, $posY, $width, $height)
    {
        parent::__construct($posX, $posY);

        $this->_width = $width;
        $this->_height = $height;
    }

    public function height() 
    {
        return $this->_height;
    }

    public function width()
    {
        return $this->_width;
    }

    public function toArray()
    {
        return [ 
            'x' => $this->X(), 
            'y' => $this->Y(), 
            'width' => $this->width(), 
            'height' => $this->height()
        ];
    }
}