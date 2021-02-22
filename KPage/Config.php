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

namespace KuaKee;

final class Config
{ 
    static private $_instance = null;
    static private $_config = null;

    private function __construct($config)
    {
        if( is_string($config) ) {
            self::$_config = include_once $config;
        }
        else {
            self::$_config = $config;
        }

        if(empty(self::$_config)) {
            throw new Exception('Config 配置信息丢失！');
        }
    }
    
    /**
     * 创建 Config 
     * @param String/Array $config 配置信息
     * @return \Kuakee\Config 返回当前实例
     */
    static public function createInstance($config)
    {
        if(!self::$_instance) {
            self::$_instance = new self($config);
        }

        return self::$_instance;
    }

    /**
     * 读取 config 内容
     * @param String $key 指定的 key 值，可以为 null，如果为 null 读取全部 config 内容，层级符号“.”
     * @param Mixed  $defaultValue 默认值，如果指定 $key 的值不存在，返回默认值
     * @return String/Array 返回Config值
     */
    static public function get($key = null, $defaultValue = null) 
    {
        // 如果 key 没有设置，返回全部的内容
        if(empty($key)) {
            return self::$_config;
        }

        // 键值根据 . 分隔，然后逐层读取到数据
        $tmp = self::$_config;
        foreach(explode('.', $key) as $k) {
            if(!array_key_exists($k, $tmp)) {
                return $defaultValue;
            }
 
            $tmp = $tmp[$k]; 
        }

        return $tmp;
    }

    private function setArrayRecursive($keys, &$arr, $value) 
    {
        $key = array_shift($keys);
        if(!$key) {
            return;
        }

        if(count($keys) == 0) {
            $arr[$key] =  $value;
            return;
        }

        $arr[$key] = [];

        return $this->setArrayRecursive($keys, $arr[$key], $value);
    }

    /**
     * 设置 config 中 $key 的值
     * @param String $key 如果是多层级，用 . 做层级符号
     * @param Mixed $value
     */
    static public function set($key, $value) 
    {
        if(!is_string($key)) {
            return false;
        }

        $tmp = [];
        self::$_instance->setArrayRecursive(\explode('.', $key), $tmp, $value);
        self::$_config = array_replace_recursive(self::$_config, $tmp);

        return true;
    }

}