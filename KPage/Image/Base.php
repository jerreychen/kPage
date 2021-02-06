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

abstract class Base
{
    protected $img_source = null;   // image source
    protected $img_mime = null; // image type
 
    public function __construct() 
    {
    }
 
    // 从文件载入图片
    public function loadFromFile($filePath)
    {
        switch($this->img_mime) {
            case 'image/gif':
                $this->img_source = imagecreatefromgif($filePath);
                break;
            case 'image/png': 
                $this->img_source = imagecreatefrompng($filePath);
                break;
            case 'image/jpeg': 
                $this->img_source = imagecreatefromjpeg($filePath);
                break;
        }
 
        return new Brush($this->img_source);
    }
    
    /**
     * 创建新的笔刷
     * @param Integer $width
     * @param Integer $height
     * @param String $bgColor 背景色
     * @param Bollean $pct 透明度
     */
    public function newImage($width, $height, $bgColor = '#FFFFFF', $pct = false) 
    {
        $this->img_source = \imagecreatetruecolor($width, $height);
 
        $colorID = Color::create($this->img_source)->getColorID($bgColor, $pct);
        
        \imagefill($this->img_source, 0, 0, $colorID);
 
        return new Brush($this->img_source);
    }

    /**
     * 向图片写入字符串
     * @param String $text
     * @param String $fontFile
     * @param Float $fontSize
     * @param Position $pos
     * @param Float $angle
     * @param string $textColor
     * @return Postion
     */
    public function writeText($text, $fontFile, $fontSize = 9, $pos = null, $angle = 0, $textColor = '#000000')
    {
        if(is_null($pos)) {
            $pos = new Position(0, 0);
        }
        if(!$pos instanceof Position) {
            throw new \Exception('参数 $imagePos 必须是 KuaKee\Image\Position 对象');
        }
        if(!\is_file($fontFile)) {
            throw new \Exception('字体文件不存在！');
        }
 
        $colorID = Color::create($this->img_source)->getColorID($textColor);

        $height = 0;
        $width = 0;
        /*
            返回一个含有 8 个单元的数组表示了文本外框的四个角：
            0	左下角 X 位置
            1	左下角 Y 位置
            2	右下角 X 位置
            3	右下角 Y 位置
            4	右上角 X 位置
            5	右上角 Y 位置
            6	左上角 X 位置
            7	左上角 Y 位置 
        */
        $box = imagettfbbox($fontSize, $angle, $fontFile, $text);
        if($box) {
            $min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
            $max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
            $min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
            $max_y = max( array($box[1], $box[3], $box[5], $box[7]) );
            $width  = ( $max_x - $min_x );
            $height = ( $max_y - $min_y ); 
        }
        imagettftext($this->img_source, $fontSize, $angle, $pos->X(), $pos->Y() + $height, $colorID, $fontFile, $text);

        return new Position($pos->X() + $width, $pos->Y() + $height);
    }
 
    /** 
     * 截取部分图像
     * @param Rectangle $rect
     */
    public function crop($rect)
    {
        imagecrop($this->img_source, $rect->toArray());
    }
    public function flipX()
    {
        imageflip($this->img_source, IMG_FLIP_HORIZONTAL);
    }
    public function flipY()
    {
        imageflip($this->img_source, IMG_FLIP_VERTICAL);
    }
    public function flipXY()
    {
        imageflip($this->img_source, IMG_FLIP_BOTH);
    }

    public function scale($width, $height)
    {
       $this->img_source = imagescale($this->img_source, $width, $height);
    }
    public function rotate($angle, $bgColor = '')
    {
        $colorID = Color::create($this->img_source)->getColorID($bgColor);
        $this->img_source = imagerotate($this->img_source, $angle, $colorID);
    }

    /**
     * 按比例缩放图片
     * @param Float $percent
     * @return Boolean
     */
    public function zoom($percent)
    {
        if($percent <= 0) {
            return false;
        }

        $width = imagesx($this->img_source);
        $height = imagesy($this->img_source);

        $newWidth = intval( floor($width * $percent / 100) );
        $newHeight = intval( floor($height * $percent / 100) );

        // 创建透明背景容器
        $dest = imagecreatetruecolor($newWidth, $newHeight);
        $transColorID = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $transColorID);

        $result = imagecopyresampled($dest, $this->img_source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        if($result) {
            imagedestroy($this->img_source);
            $this->img_source = $dest;
        }

        return $result;
    }

    /** 
     * 将地址 $imageFile 图像的指定区域合并到当前图像的指定位置中
     * @param String $imageFile
     * @param Integer $pct 0~100 透明度
     * @param Rectangle $rect
     * @param Position $pos 
     */
    public function attachFrom($imageFile, $pct = 0, $rect = null, $pos = null)
    {
        if(is_null($pos)) {
            $pos = new Position(0, 0);
        }

        $imageData = file_get_contents($imageFile);
        list($width, $height) = getimagesizefromstring($imageData);
        if(is_null($rect)) {
            $rect = new Rectangle(0, 0, $width, $height);
        }

        if($rect->X() > $width || $rect->Y() > $height
            || ($rect->X() + $rect->width() > $width)
            || ($rect->Y() + $rect->height() > $height)) {
            throw new \Exception('截取的图像大小超出图片范围！');
        }

        $source = imagecreatefromstring($imageData);

        imagecopymerge($this->img_source, $source, 
            $pos->X(), $pos->Y(), $rect->X(), $rect->Y(), 
            min($width, $rect->width()), min($height, $rect->height()), 
            $pct);
        
        imagedestroy($source);
    }

    /** 
     * 将当前图像合并到指定地址 $imageFile 图像的指定位置中
     * @param String $imageFile
     * @param Integer $pct 0~100 透明度
     * @param Position $pos
     * @return void
     */
    public function attachTo($imageFile, $pct = 0, $pos = null)
    {
        if(is_null($pos)) {
            $pos = new Position(0, 0);
        }

        $imageData = file_get_contents($imageFile);
        list($width, $height) = getimagesizefromstring($imageData);
        $dest = imagecreatefromstring($imageData);

        $sWidth = imagesx($this->img_source);
        $sHeight = imagesy($this->img_source);

        imagecopymerge($dest, $this->img_source,
            $pos->X(), $pos->Y(), 0, 0, 
            min($width, $sWidth), min($height, $sHeight), 
            $pct);
        
        imagedestroy($this->img_source);
        $this->img_source = $dest;
    }

    public function save($filePath = null)
    {
        if(empty($filePath)) {
            header('HTTP/1.0 200 Ok');
            header("Content-type: " . $this->img_mime);
        }

        imagealphablending($this->img_source, false);
        imagesavealpha($this->img_source, true);
        imageantialias($this->img_source, true);

        switch($this->img_mime) {
            case 'image/gif':
                imagegif($this->img_source, $filePath);
                break;
            case 'image/png':
                imagepng($this->img_source, $filePath);
                break;
            case 'image/jpeg':
                imagejpeg($this->img_source, $filePath);
                break;
        }

        Brush::destroy();
        imagedestroy($this->img_source);
    }
}