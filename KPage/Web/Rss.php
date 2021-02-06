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

final class Rss
{
    const NODE_VALUE_KEY = '__node_value__';
    const NODE_ATTRS_KEY = '__node_attrs__';
    const NODE_ITEMS_KEY = '__node_items__';
    const NODE_IS_CDATA = '__node_is_cdata__';

    const WEEK_DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private $_xml = null,
            $_channel = null;

    private $_node_list = null;
    private $_skip_days = null;
    private $_skip_hours = null;
    private $_item_list = null;

    public function __construct($version = '2.0')
    {
        $this->_xml = new XML();

        // 添加根节点
        $rss = $this->_xml->createNode('rss', '', ['version' => $version]);

        // 添加 channel 节点
        $this->_channel = new \DOMElement('channel');
        $rss->appendChild($this->_channel);

        // 初始化
        $this->_node_list = [];
        $this->_skip_days = [];
        $this->_skip_hours = [];
        $this->_item_list = [];
    }
    
    private function setNode($nodeName, $value, $attrs = [], $isCData = false)
    {
        $this->_node_list[$nodeName]  = [ 
            self::NODE_VALUE_KEY => $value,
            self::NODE_ATTRS_KEY => $attrs, 
            self::NODE_IS_CDATA => $isCData
        ];
    }

    /**
     * <title></title>
     * @param String $title
     * @param Boolean $isCData
     */
    public function setTitle($title, $isCData = false) 
    {
        $this->setNode('title', $title, [], $isCData);
    }

    /**
     * <description></description>
     * @param String $description
     * @param Boolean $isCData
     */
    public function setDescription($description, $isCData = false)
    {
        $this->setNode('description', $description, [], $isCData);
    }

    /**
     * <copyright></copyright>
     * @param String $copyright
     * @param Boolean $isCData
     */
    public function setCopyright($copyright, $isCData = false)
    {
        $this->setNode('copyright', $copyright, [], $isCData);
    }

    /**
     * <link></link>
     * @param String $link
     */
    public function setLink($link) 
    {
        $this->setNode('link', $link);
    }

    /**
     * <category domain=""></category>
     * @param String $category
     * @param String $domain
     */
    public function setCategory($category, $domain = '') 
    {
        $this->setNode('category', $category, [ 'domain' => $domain ]);
    }

    /**
     * <generator></generator>
     * @param String $generator
     */
    public function setGenerator($generator)
    {
        $this->setNode('generator', $generator);
    }

    /**
     * 用于指定编写 RSS 文档时所使用的语言 (语言代码查看：https://www.rssboard.org/rss-language-codes)
     * <language></language>
     * @param String $language
     */
    public function setLanguage($language)
    {
        $this->setNode('language', $language);
    }

    /**
     * 规定指向当前 RSS 文件所用格式说明的 URL
     * <docs></docs>
     * @param String $docs
     */
    public function setDocs($docs)
    {
        $this->setNode('docs', $docs);
    }

    /**
     * 定义 feed 内容的最后修改日期
     * <lastBuildDate></lastBuildDate>
     * @param String $lastBuildDate
     */
    public function setLastBuildDate($lastBuildDate)
    {
        $this->setNode('lastBuildDate', $lastBuildDate);
    }

    /**
     * 为 feed 的内容定义最后发布日期
     * <pubDate></pubDate>
     * @param String $pubDate
     */
    public function setPubDate($pubDate)
    {
        $this->setNode('pubDate', $pubDate);
    }

    /**
     * (ttl=time to live) 指定在 feed 源更新此 feed 之前，feed 可被缓存的分钟数
     * <ttl></ttl>
     * @param Integer $ttl
     */
    public function setTTL($ttl)
    {
        $this->setNode('ttl', $ttl);
    }

    /**
     * 定义 feed 网络管理员的电子邮件地址
     * <webMaster></webMaster>
     * @param String $webMaster
     */
    public function setwebMaster($webMaster)
    {
        $this->setNode('webMaster', $webMaster);
    }

    /**
     * 定义 feed 内容编辑的电子邮件地址
     * <managingEditor></managingEditor>
     * @param String $managingEditor
     */
    public function setManagingEditor($managingEditor)
    {
        $this->setNode('managingEditor', $managingEditor);
    }

    /**
     * 规定在那些天，聚合器忽略更新 feed，最多可以用 7 个 <skipDays> 元素。
     * <skipDays>
     *  <day></day>
     * </skipDays>
     * @param Integer $day
     */
    public function addSkipDays($day)
    {
        if(is_numeric($day) && ($day < 0 || $day > 6)) {
            throw new \Exception('只允许数字，0 表示星期一，6 表示星期天！');
        }

        if(count($this->_skip_days) >= 7) {
            throw new \Exception('最多可以用 7 个 <skipDays> 元素！');
        }
 
        $day = self::WEEK_DAYS[$day];
        if(in_array($day, $this->_skip_days)) {
            return;
        }

        array_push($this->_skip_days, $day);
    }

    /**
     * 规定在那些小时，聚合器忽略更新 feed，最多可以用 24 个 <skipHours> 元素，0 表示午夜。
     * <skipHours>
     *  <hour></hour>
     * </skipHours>
     * @param Integer $hour
     */
    public function addSkipHours($hour)
    { 
        if(is_numeric($hour) && ($hour < 0 || $hour > 23)) {
            throw new \Exception('只允许数字，0 表示午夜12点，最大值 23！');
        }

        if(count($this->_skip_hours) >= 24) {
            throw new \Exception('最多可以用 24 个 <skipHours> 元素，0 表示午夜！');
        }

        if(in_array($hour, $this->_skip_hours)) {
            return;
        }

        array_push($this->_skip_hours, $hour);
    }

    /**
     * 该图片必须是 GIF、JPEG 或 PNG 类型
     * @param String $url 定义图像的 URL
     * @param String $title 定义当图片不能显示时所显示的替代文本
     * @param String $link 定义提供该频道的网站的超连接
     * @param String $description
     * @param Integer $height 定义图像的高度。默认是 31。最大值是 400
     * @param Integer $width 定义图像的宽度。默认是 88。最大值是 144
     */
    public function setImage($url, $title, $link, $description = '', $height = 31, $width = 88)
    {
        $height = max(31, min($height, 400));
        $width = max(88, min($width, 144));

        $this->setNode('image', [
            'url'           => $url,
            'title'         => $title,
            'link'          => $link,
            'description'   => $description,
            'height'        => $height,
            'width'         => $width
        ]);
    }

    /**
     * <cloud domain="" port="80" path="" registerProcedure="" protocol="xml-rpc" />
     * @param String $domain
     * @param Integer $port
     * @param String $path
     * @param String $registerProcedure
     * @param String $protocol
     */
    public function setCloud($domain, $port = 80, $path = '', $registerProcedure = '', $protocol = 'xml-rpc')
    {
        $this->setNode('cloud', true, [ 
            'domain'    => $domain,
            'port'      => $port,
            'path'      => $path,
            'registerProcedure' => $registerProcedure,
            'protocol'  => $protocol
        ]);
    }
 
    /**
     * 添加item
     * @param RssItem $item
     */
    public function addItem($item)
    {
        if(!$item instanceof RssItem) {
            throw new \Exception('只允许添加 RssItem 实例！');
        }

        array_push($this->_item_list, $item);
    }

    private function generateCommonNodes()
    {
        if(!array_key_exists('title', $this->_node_list)
            || !array_key_exists('link', $this->_node_list)
            || !array_key_exists('description', $this->_node_list)) {
                throw new \Exception('描述 RSS feed，有三个必需的子元素：title, link, description！');
        }

        foreach($this->_node_list as $node => $config) {
            $nodeValue = $config[self::NODE_VALUE_KEY] ?? '';

            $domNode = new \DOMElement($node);
            $this->_channel->appendChild($domNode);

            if(is_array($nodeValue)) {
                foreach($nodeValue as $key => $value) {
                    $subNode = new \DOMElement($key, $value);
                    $domNode->appendChild($subNode);
                }
            }
            else {
                if($config[self::NODE_IS_CDATA]) {
                    $domNode->appendChild(new \DOMCdataSection($nodeValue));
                }
                else {
                    $domNode->appendChild(new \DOMText($nodeValue));
                }
            }

            foreach($config[self::NODE_ATTRS_KEY] as $key => $value) { 
                $domNode->setAttribute($key, $value);
            }
        }
    }

    private function generateSkipNodes($nodeName, $subNodeName, $items)
    {
        if(count($items) == 0) {
            return;
        }

        $skipNode = new \DOMElement($nodeName);
        $this->_channel->appendChild($skipNode); 

        foreach($items as $item) { 
            $subNode = new \DOMElement($subNodeName, $item);
            $skipNode->appendChild($subNode); 
        } 
    }

    private function generateItems()
    {
        if(count($this->_item_list) == 0) {
            return;
        }

        foreach($this->_item_list as $item) {
            $itemNode = new \DOMElement('item');
            $this->_channel->appendChild($itemNode);

            foreach($item->getItemNodes() as $key => $config) {
                $subNode = new \DOMElement($key);
                $itemNode->appendChild($subNode);

                $nodeValue = $config[self::NODE_VALUE_KEY] ?? '';
                if($config[self::NODE_IS_CDATA]) {
                    $subNode->appendChild(new \DOMCdataSection($nodeValue));
                }
                else {
                    $subNode->appendChild(new \DOMText($nodeValue));
                }

                foreach($config[self::NODE_ATTRS_KEY] as $key => $value) { 
                    $subNode->setAttribute($key, $value);
                } 
            }
        }
    }

    private function generateXml()
    {
        $this->generateCommonNodes();

        $this->generateSkipNodes('skipDays', 'day', $this->_skip_days);

        $this->generateSkipNodes('skipHours', 'hour', $this->_skip_hours);

        $this->generateItems();
    }

    /**
     * 返回原始的 xml
     */
    public function getXml()
    {
        $this->generateXml();

        return $this->_xml->getXml();        
    }

    /**
     * 返回经过 xsl 样式处理之后的 xml
     */
    public function transformXml($xslFile) 
    {
        $this->generateXml();

        return $this->_xml->transformXml($xslFile);
    }

}