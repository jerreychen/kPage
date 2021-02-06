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

namespace KuaKee\Web;

final class HTML
{
    private $_html = null;
    private $_dom = null;

    public function __construct($html)
    {
        $this->_html = mb_convert_encoding($html, 'UTF-8', ["ASCII",'UTF-8',"GB2312","GBK",'BIG5']);

        libxml_use_internal_errors(true);

        $this->_dom = new \DOMDocument();
        
        $encoding = $this->getEncoding();

        $pureHtml = $this->getPureHtml($this->_html);

        $this->_dom->encoding = $this->getEncoding();
        $pureHtml = mb_convert_encoding($pureHtml, 'HTML-ENTITIES', $this->_dom->encoding);
 
        if(!$this->_dom->loadHTML($pureHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            throw new \Exception('HTML格式化转换失败！');
        }
    }

    private function getEncoding()
    {
        $rgx = '/<meta[^>]*?charset="(?<charset>.*?)"[^>]*?\/>/';
        if(preg_match($rgx, $this->_html, $matches)) {
            return $matches['charset'];
        }
        return 'UTF-8';
    }

    public function getHtml()
    {
        return $this->_html;
    }

    public function getPureHtml()
    {
        return preg_replace_callback_array([
            '/<style[\s\S]*?<\/style>/im' => function($match) {
                return '';
            },
            '/<script[\s\S]*?<\/script>/im' => function($match) {
                return '';
            },
            '/<!--[\s\S]*?-->/m' => function($match) {
                return '';
            },
            '/<(link|meta).*?>/' => function($match) {
                return '';
            },
            '/\n\s+\n/m' => function($match) { return "\n"; },
            '/>\n+</m' => function($match) { return ">\n<"; }
        ], $this->_html);
    }

    public function getTitle()
    {
        $regex = '/<(title)>(?<title>[\s\S]*?)<\/\1>/i';
        if(preg_match($regex, $this->_html, $matches)) {
            return $matches['title'];
        }

        return '';
    }

    public function getCssFiles()
    {
        $regexCssFiles = '/<link\s.*?>/is';
        if(preg_match_all($regexCssFiles, $this->_html, $matches)) {
            $result = [];

            foreach($matches[0] as $match) {
 
                if(preg_match('/href=(\'|")(?<href>.*?)\1/i', $match, $linkMatches)) {
                    array_push($result, $linkMatches['href']);
                }

            }

            return $result;
        }

        return [];
    }

    public function getScriptFiles()
    {
        $regexScriptFiles = '/<script\s.*?>/is';
        if(preg_match_all($regexScriptFiles, $this->_html, $matches)) {
            $result = [];
            
            foreach($matches[0] as $match) {
 
                if(preg_match('/src="(?<src>.*?)"/i', $match, $scriptMatches)) {
                    array_push($result, $scriptMatches['src']);
                }
            }

            return $result;
        }

        return []; 
    }

    /**
     * 根据xpath查询
     * @param String $queryPath
     * @return Boolean/String/DOMNodeList 返回第一个匹配元素的html；或者返回匹配的元素集合（DOMNodeList）；或者如果没有查询到，返回false
     */
    public function xquery($queryPath, $singleResult = true)
    {
        $xpath = new \DOMXpath($this->_dom);
        $nodes = $xpath->query($queryPath);

        if(!$nodes || $nodes->count() == 0) {
            return false;
        }

        if($singleResult) {
            return $nodes->item(0)->C14N();
        }

        return $nodes;
    }

    /**
     * 根据 class 查询元素
     * @param String $className
     * @param String $tagName 默认：div
     * @param Boolean $singleResult 是否只返回第一个匹配
     * @return Boolean/String/Array 返回第一个匹配元素的html；或者返回匹配的元素集合（数组）；或者如果没有查询到，返回false
     */
    public function getElementByClass($className, $tagName = 'div', $singleResult = true)
    { 
        return $this->xquery("*//".$tagName."[@class='".$className."']", $singleResult);
    }

    /**
     * 根据 id 查询元素
     * @param String $elementID
     * @return String 返回元素html
     */
    public function getElementByID($elementID) 
    {
        $element = $this->_dom->getElementById($elementID);
        return $element->C14N();
    }

    /**
     * 取得 xml 文档内容
     */
    public function getXml()
    {
        return $this->_dom->saveHTML();
    }
}