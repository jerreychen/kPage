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

final class Brush
{ 
    private $_instance = null;
    private $_img_source = null;   // image source
    private $_brush_source = null;
    private $_brush_style = false;
    private $_brush_color_id = 0;

    public function __construct($imgSource)
    {
        $this->_img_source = $imgSource;
    }

    static public function destroy()
    {
        if(!self::$_instance instanceof self) {
            return;
        }

        if(!self::$_instance->_brush_source) { 
            return;
        }
        
        imagedestroy(self::$_instance->_brush_source);
    }
 
    /**
     * 设置笔刷样式（样色、宽度）
     * @param String $color
     * @param Integer $thickness
     */
    public function setBrush($color, $thickness = 1)
    {
        // 设置画线的颜色
        $this->_brush_color_id = Color::create($this->_img_source)->allocateColorID($color);
        // 设置画线宽度
        imagesetthickness($this->_img_source, $thickness);
    }

    public function setBrushImage($imageFile, $transparentColor = '')
    {
        $this->_brush_source = imagecreatefromstring(file_get_contents($imageFile));
        if(!empty($transparentColor)) {
            $colorID = Color::create($this->_brush_source)->getColorID($transparentColor);
            imagecolortransparent($this->_brush_source, $colorID);
        }
        imagesetbrush($this->_img_source, $this->_brush_source);
    }

    public function setBrushStyle($style)
    {
        if(!\is_array($style)) {
            return;
        }

        imagesetstyle($this->_img_source, $style);
        $this->_brush_style = true;
    }

    private function getBrushColorID()
    {
        if(!$this->_brush_source && !$this->_brush_style) {
            return $this->_brush_color_id;
        }

        if(!$this->_brush_source) {
            return IMG_COLOR_STYLED;
        }

        if(!$this->_brush_style) {
            return IMG_COLOR_BRUSHED;
        }

        return IMG_COLOR_STYLEDBRUSHED;
    }

    public function drawLine($posStart, $posEnd)
    {
        imageline($this->_img_source, $posStart->X(), $posStart->Y(), $posEnd->X(), $posEnd->Y(), $this->getBrushColorID());
    }

    public function drawPixel($pos)
    {
        imagesetpixel($this->_img_source, $pos->X(), $pos->Y(), $this->getBrushColorID());
    }

    public function drawPolygon($posList)
    {
        $points = [];
        foreach($posList as $pos) {
            array_push($points, $pos->X(), $pos->Y());
        }
        imagepolygon($this->_img_source, $points, count($posList), $this->getBrushColorID());
    }

    public function drawEllipse($posCenter, $width, $height)
    {
        imageellipse($this->_img_source, $posCenter->X(), $posCenter->Y(), $width, $height, $this->getBrushColorID());
    }
}