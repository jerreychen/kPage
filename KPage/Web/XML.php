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

final class XML
{
    private $_xml = null;

    public function __construct($version = '1.0', $encoding = 'UTF-8')
    {
        $this->_xml = new \DOMDocument($version, $encoding);
    }

    public function loadXml($xml)
    {
        $this->_xml->loadXML($xml);
    }

    /**
     * 创建 xml node
     * @param String $nodeName
     * @param String $nodeValue
     * @param Array $attrs
     */
    public function createNode($nodeName, $nodeValue, $attrs = [])
    {
        $node = new \DOMElement($nodeName, $nodeValue);
        $this->_xml->appendChild($node);

        foreach($attrs as $key => $value) {
            $node->setAttribute($key, $value);
        }

        return $node;
    }

    /**
     * 返回经过 xsl 样式处理之后的 xml
     * @param String $xslFile
     * @return String
     */
    public function transformXml($xslFile) 
    {
        if(!is_file($xslFile)) {
            throw new \Exception('文件：'. $xslFile .'不存在');
        }

        $xsl = new \DOMDocument;
        $xsl->load($xslFile);

        $proc = new \XSLTProcessor();
        $proc->importStyleSheet($xsl);
        
        // transform $xml according to the stylesheet $xsl
        return $proc->transformToXML($this->_xml); // transform the data
    }

    public function getXml()
    {
        return $this->_xml->saveXML();
    }
}