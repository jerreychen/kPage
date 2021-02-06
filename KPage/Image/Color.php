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

final class Color
{
    static private $_instance = null;
    private $_img_source = null;

    private function __construct()
    {

    }

    static public function create($imgSource)
    {
        if(!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        self::$_instance->_img_source = $imgSource;

        return self::$_instance;
    }

    /** 
     * 取得 color 的 rgb 整型数
     * @param String $colorCode
     * @return Array/Boolean(false)
     */
    private function getColor($colorCode) 
    {
        if(\preg_match('/#([a-fA-F0-9]{6})/', $colorCode, $matches)) {
            $r = hexdec( substr($matches[1], 0, 2) );
            $g = hexdec( substr($matches[1], 2, 2) );
            $b = hexdec( substr($matches[1], 4, 2) );

            return [ 'red' => $r, 'green' => $g, 'blue' => $b ];
        }

        return false;
    }

    /**
     * 为一幅图像分配颜色
     * @param String $colorCode
     * @param Integer $alpha 默认值是 false：忽略透明度
     * @return Integer/Boolean(false)
     */
    public function allocateColorID($colorCode, $alpha = false)
    {
        $color = $this->getColor($colorCode);
        if(!$color) {
            return 0;
        }

        $r = $color['red'];
        $g = $color['green'];
        $b = $color['blue'];
        
        if(is_integer($alpha)) {
            return \imagecolorallocatealpha($this->_img_source, $r, $g, $b, $alpha);
        }

        return \imagecolorallocate($this->_img_source, $r, $g, $b);
    }

    /**
     * 取得与指定的颜色最接近的颜色的索引值
     * @param String $colorCode
     * @param Integer $alpha 默认值是 false：忽略透明度
     * @return Integer/Boolean(false)
     */
    public function getColorID($colorCode, $alpha = false)
    {
        $color = $this->getColor($colorCode);
        if(!$color) {
            return 0;
        }

        $r = $color['red'];
        $g = $color['green'];
        $b = $color['blue'];
        
        if(is_integer($alpha)) {
            return \imagecolorclosestalpha($this->_img_source, $r, $g, $b, $alpha);
        }

        return \imagecolorclosest($this->_img_source, $r, $g, $b);
    }

    /**
     * 取得指定颜色的索引值
     * @param String $colorCode
     * @param Integer $alpha 默认值是 false：忽略透明度
     * @return Integer/Boolean(false)
     */
    public function getExactColorID($colorCode, $alpha = false) 
    {
        $color = $this->getColor($colorCode);
        if(!$color) {
            return 0;
        }

        $r = $color['red'];
        $g = $color['green'];
        $b = $color['blue'];
        
        if(is_integer($alpha)) {
            return \imagecolorexactalpha($this->_img_source, $r, $g, $b, $alpha);
        }

        return \imagecolorexact($this->_img_source, $r, $g, $b);
    }
    
    /**
     * 取得指定颜色的索引值或有可能得到的最接近的替代值
     * @param String $colorCode
     * @param Integer $alpha 默认值是 false：忽略透明度
     * @return Integer/Boolean(false)
     */
    public function getResolveColorID($colorCode, $alpha = false) 
    {
        $color = $this->getColor($colorCode);
        if(!$color) {
            return 0;
        }

        $r = $color['red'];
        $g = $color['green'];
        $b = $color['blue'];
        
        if(is_integer($alpha)) {
            return \imagecolorresolvealpha($this->_img_source, $r, $g, $b, $alpha);
        }

        return \imagecolorresolve($this->_img_source, $r, $g, $b);
    }
}