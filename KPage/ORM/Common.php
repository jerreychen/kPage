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

namespace KuaKee\ORM;

final class Common
{
    const WILDCARD = ':';

    private function __construct() {}

    /**
     * 得到查询字段列表
     * @param String/Array $fields 查询字段
     * @return Array
     */
    static public function getFields($fields)
    {
        if(is_array($fields)) {
            return $fields;
        }

        if(is_string($fields)) {
            // 取出字段
            return array_map(function($f) {
                return trim($f); // 移除左右空格
            }, explode(',', $fields));
        }
        
        return [];
    }
        
    /**
     * 转换字段名，使它带表别名
     * 如果字段内含有聚合函数，那么返回聚合函数带字段别名
     * @param String $fieldName 字段名
     * @param String $tableAliasName 表别名
     * @return String
     */
    static public function aliasFieldName($fieldName, $alias)
    {   
        if(stripos($fieldName, '.') > 0) {
            return $fieldName;
        }
 
        $alias = empty($alias) ? $alias : ('`'. $alias . '`.');
        if($fieldName === '*') {
            return $alias . $fieldName;
        }
        
        $patten = '/(?<name>\w+)\((?<field>([\w\*\'\"-]+)?)\)/';
        // 判断fieldName 中是否有函数
        if(!preg_match($patten, $fieldName)) {
            $pos_as =stripos($fieldName, 'AS');

            $fieldName_for_replace = $pos_as > 0 ? substr($fieldName, 0, $pos_as) : $fieldName;
            $fieldName_as = $pos_as > 0 ? substr($fieldName, $pos_as) : '';

            return preg_replace_callback('/\w+/', function($matches) use ($alias) {
                return $alias . '`'. $matches[0] . '`';
            }, $fieldName_for_replace) . $fieldName_as;
        }

        return preg_replace_callback($patten, function($matches) use ($alias) {
            
            if(is_numeric($matches['field'])    // 函数参数是：数字
                || $matches['field'] == '*'     // 函数参数是： *
                || empty($matches['field'])     // 函数参数是空
                || preg_match('/^([\'\"])\w+\1$/', $matches['field'])) { //函数参数以 "（双引号）或者 '（单引号）开头结尾
                return $matches[0];
            }

            return $matches['name']. '(' . $alias . '`' . $matches['field'] . '`)';
        }, $fieldName); 
    }

    /**
     * 给字段增加别名
     */
    static public function aliasFields($fields, $alias = '')
    {
        return array_map(function($fn) use ($alias) {
            return self::aliasFieldName($fn, $alias);
        }, $fields);
    }

    /**
     * 将 where 条件进行格式化
     * @param String/Array $dataBind
     * @param String $prefix
     * @return String
     */
    static public function generateWhereClause($whereClause, $prefix = '')
    { 
        // 如果不是数组，那么返回错误提示
        if(is_array($whereClause)) { 

            $whereClause = implode(' AND ', array_map(function($clause) {

                // 第二维如果是数组，组合成 OR 查询
                if(is_array($clause) && count($clause) > 0) {
                    return '(' . implode(' OR ', $clause) . ')';
                }

                return $clause;

            }, $whereClause));
        }

        if(empty($prefix)) {
            return $whereClause;
        }

        return preg_replace('/:(\w+)/i', self::WILDCARD . $prefix . '_${1}', $whereClause);
    }

    /**
     * 将where条件的数据key进行格式化
     * @param Array $dataBind
     * @param String $prefix
     * @return Array
     */
    static public function generateBindData($dataBind, $prefix = '')
    {
        if(empty($prefix) or empty($dataBind)) {
            return $dataBind;
        }

        $result = [];
        foreach($dataBind as $key => $value) {
            $result[self::WILDCARD. $prefix .'_'. substr($key, 1)] = $value;
        }
        
        return $result;
    }
}