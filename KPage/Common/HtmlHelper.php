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

namespace KuaKee\Common;

use \KuaKee\Request;

final class HtmlHelper
{
    static private $_instance;

    private function __construct()
    {
    }

    static public function getInstance()
    {
        if(!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function getPagerUrl($pageIndex) 
    {
        $baseUri = Request::getInstance()->uri();
        $query = Request::getInstance()->query();
        
        if(isset($query['p'])) {
            $query['p'] = $pageIndex;
            return $baseUri . '?' . http_build_query($query);
        }
        
        return $baseUri . '?' . http_build_query($query) . '&p=' . $pageIndex;
    }

    public function renderPager($pageSize, $pageIndex, $rowCount)
    {
        $html = [];

        // 第一页链接
        if ($pageIndex <= 0) {
            array_push($html, '<li class="page-item disabled"><span class="page-link">第一页</span></li>');
        }
        else {
            array_push($html, '<li class="page-item"><a class="page-link" href="'. $this->getPagerUrl(0) .'">第一页</a></li>');
        }
        // 上一页
        if ($pageIndex <= 0) {
            array_push($html, '<li class="page-item disabled"><span class="page-link">上一页</span></li>');
        }
        else {
            array_push($html, '<li class="page-item"><a class="page-link" href="'. $this->getPagerUrl($pageIndex - 1) .'">上一页</a></li>');
        }

        $pageTotal = ceil($rowCount / $pageSize);
        $startPageIndex = 0;
        $endPageIndex = $pageTotal;

        if($pageTotal - $pageIndex > 3) {
            $endPageIndex = ($pageIndex > 2) ? $pageIndex + 3 : min(5, $pageTotal);
        }

        if($endPageIndex - $startPageIndex > 5) {
            $startPageIndex = $endPageIndex -5;
        }

        for($i = $startPageIndex; $i < $endPageIndex; $i++) {
            if($i == $pageIndex) { 
                array_push($html, '<li class="page-item active" aria-current="page"><span class="page-link">'. ($i + 1) .'<span class="sr-only">(current)</span></span></li>');
            }
            else {
                array_push($html, '<li class="page-item"><a class="page-link" href="'. $this->getPagerUrl($i) .'">'. ($i + 1) .'</a></li>');
            }
        }

        // 下一页
        if ($pageIndex < $pageTotal - 1) { 
            array_push($html, '<li class="page-item"><a class="page-link" href="'. $this->getPagerUrl($pageIndex + 1) .'">下一页</a></li>');
        }
        else {
            array_push($html, '<li class="page-item disabled"><span class="page-link">下一页</span></li>');
        }
        // 最后一页
        if($pageIndex >= $pageTotal - 1) { 
            array_push($html, '<li class="page-item disabled"><span class="page-link">最后一页</span></li>');
        }
        else {
            array_push($html, '<li class="page-item"><a class="page-link" href="'. $this->getPagerUrl($pageTotal - 1) .'">最后一页</a></li>');
        }

        return implode('', $html);
    }
    
}