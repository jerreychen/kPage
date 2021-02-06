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

final class RssItem
{
    private $_item_nodes = null;

    public function __construct($title, $link, $description, $author = '', $category = '', $comments = '', $pubDateTime = 0, $guid = '')
    {
        if(empty($title) || empty($link) || empty($description)) {
             throw new \Exception('定义 RSS feed 中的一篇文章，有三个必需的子元素：title，link，description！');
        }

        $this->setNode('title', $title);
        $this->setNode('link', $link);
        $this->setNode('description', $description, [], true);

        if(!empty($author)) { $this->setAuthor($author); }
        if(!empty($category)) { $this->setCategory($category); }
        if(!empty($comments)) { $this->setComments($comments); }
        if(!empty($pubDateTime)) { $this->setPubDateTime($pubDateTime); }
        if(!empty($guid)) { $this->setGuid($guid); }
    }

    private function setNode($nodeName, $value, $attrs = [], $isCData = false)
    { 
        $this->_item_nodes[$nodeName] = [ 
            RSS::NODE_VALUE_KEY => $value, 
            RSS::NODE_ATTRS_KEY => $attrs,
            RSS::NODE_IS_CDATA => $isCData
        ];
    }

    /**
     * 规定项目作者的电子邮件地址
     * @param String $author
     */
    public function setAuthor($author)
    {
        $this->setNode('author', $author);
    }

    /**
     * 定义项目所属的一个或多个类别
     * @param String $category
     */
    public function setCategory($category)
    {
        $this->setNode('category', $category);
    }

    /**
     * 允许项目连接到有关此项目的注释（文件）
     * @param String $comments
     */
    public function setComments($comments)
    {
        $this->setNode('comments', $comments);
    }

    /**
     * 为 item 定义一个唯一的标识符
     * @param String $guid
     * @param Boolean $isPermaLink 可选。如果设置为 true，那么阅读器会假定它是指向一个项的永久连接
     */
    public function setGuid($guid, $isPermaLink = true)
    {
        $this->setNode('guid', $guid, ['isPermaLink' => $isPermaLink]);
    }

    /**
     * 定义此项目的最后发布日期
     * @param String $pubDateTime
     */
    public function setPubDateTime($pubDateTime)
    {
        $this->setNode('pubDateTime', $pubDateTime);
    }

    /**
     * 为此项目指定一个第三方来源
     * @param String $url
     */
    public function setSource($source, $url)
    {
        $this->setNode('source', $source, ['url' => $url]);
    }
    
    /**
     * 允许把媒体文件包含在项目中
     * @param String $url
     * @param String $type
     * @param Integer $length
     */
    public function setEnclosure($url, $type, $length) 
    {
        $this->setNode('enclosure', '', ['url'=>$url, 'type'=>$type, 'length'=>$length]);
    }

    public function getItemNodes()
    {
        return $this->_item_nodes;
    }
}