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

use PDO;
use Exception; 

final class Queryable
{
    // model 对象
    private $model;
    // 表别名
    private $tableAlias;

    // 预执行的 SQL 以数组形式保存
    private $preSelectSQL = null;
    // 预执行的绑定数据
    private $preSelectBindingData = null;

    /**
     *  构造函数
     * @param Model $model
     * @param String/Array $fields 字段列表
     * @param String $alias 表别名
    */
    public function __construct($model, $fields, $alias) 
    {
        $this->model = $model;

        // 表别名，如果未设置，别名为表名
        $this->tableAlias = $alias;

        $this->preSelectBindingData = [];

        $this->preSelectSQL = [
            'distinct'  => false,
            'fields'    => '',
            'table'     => '',
            'join'      => [], 
            'where'     => [],
            'groupBy'   => '',
            'having'    => [],
            'orderBy'   => [],
            'limit'     => ''
        ];

        $fieldsArr = $this->getArrFields($fields);
        // 如果字段是空的
        if(count($fieldsArr) == 0) {
            exit('查询字段错误！');
        }
        
        $tableName = $model->getTableName(); 

        // 为字段加上表别名
        $this->preSelectSQL['fields'] = implode(', ', Common::aliasFields($fieldsArr, $this->tableAlias));

        $this->preSelectSQL['table'] = empty($alias) ? $tableName : ("$tableName AS $alias"); 
    }
 
    /**
     * 得到查询字段列表
     * @param String/Array $fields 查询字段
     * @return Array
     */
    private function getArrFields($fields)
    {
        if($fields === '*') {
            return $this->model->getFields();
        }
        
        return Common::getFields($fields);
    }
        
    /**
     * 消除所有重复的记录，并只获取唯一一次记录。
     * @return Queryable 查询对象
     */
    public function distinct()
    {
        // 如果 distinct 已经是 true，报错
        if($this->preSelectSQL['distinct'] ) {
            throw new Exception('Cannot duplicated keyword "distinct"!');
        }

        $this->preSelectSQL['distinct'] = true;
        return $this;
    }

    /**
     * 获取数据的条件 如果满足给定的条件，当多次调用该方法执行 'AND' 条件 
     * @param String/Array $whereClause 查询条件字符串 
     * @param Array @dataBind where 条件的绑定值 如 [':id' => 1, ':name' => 'name']
     * @return Queryable 查询对象
     */
    public function where($whereClause, $dataBind = null)
    {
        // 初始化 where 语句的容器
        if(!is_array($this->preSelectSQL['where'])) {
            $this->preSelectSQL['where'] = array();
        }

        $wherePrefix = 'WHERE_'. (count($this->preSelectSQL['where']) + 1); 

        $dataBind = Common::generateBindData($dataBind, $wherePrefix);

        if(!empty($dataBind)) {
            foreach($dataBind as $key=>$value) {
                $this->preSelectBindingData[$key] = $value;
            }
        }

        array_push($this->preSelectSQL['where'], Common::generateWhereClause($whereClause, $wherePrefix));
        return $this;
    }

    /**
     * 基于一个或多个列按升序或降序顺序排列数据
     * @param String $fieldName 排序字段
     * @param String $sort 'ASC' 或 'DESC'（默认值），不区分大小写
     * @return Queryable 查询对象
     */
    public function orderBy($fieldName, $sort = 'DESC') 
    {
        // 初始化 where 语句的容器
        if(!is_array($this->preSelectSQL['orderBy'])) {
            $this->preSelectSQL['orderBy'] = array();
        }
 
        array_push($this->preSelectSQL['orderBy'], $fieldName .' '. $sort);

        return $this;
    }  
 
    /**
     * 对相同的数据进行分组。groupBy 必须放在整个查询的最后
     * @param String/Array $fields 字段名 字符串数组
     * @return Queryable 查询对象
     */
    public function groupBy($fields)
    {
        if(!empty($this->preSelectSQL['groupBy'])) {
            throw new Exception('There is a groupBy clause already!');
        }

        $fields = Common::getFields($fields);
        $groupByStr = array();
        foreach($fields as $field) {
            array_push($groupByStr, Common::aliasFieldName($field, $this->tableAlias));
        }

        $this->preSelectSQL['groupBy'] = implode(', ', $groupByStr);

        return $this;
    }

    /**
     * Group By 子句的条件。当多次调用该方法执行 'AND' 条件 
     * @param Array $havingClause 查询条件字符串 
     * @param Array @dataBind having 条件的绑定值 如 [':id' => 1, ':name' => 'name']
     * @return Queryable 查询对象
     */
    public function having($havingClause, $dataBind = null)
    {
        if(empty($this->preSelectSQL['groupBy'])) {
            throw new Exception('There must be a groupBy clause firstly!');
        }

        if(!is_array($this->preSelectSQL['having'])) {
            $this->preSelectSQL['having'] = array();
        }

        $havingPrefix = 'HAVING_'. (count($this->preSelectSQL['having']) + 1); 

        $dataBind = Common::generateBindData($dataBind, $havingPrefix);

        if(!empty($dataBind)) {
            foreach($dataBind as $key=>$value) {
                $this->preSelectBindingData[$key] = $value;
            }
        }

        array_push($this->preSelectSQL['having'], Common::generateWhereClause($havingClause, $havingPrefix));
        return $this;
    }
    
    /**
     * 获取 从 $offset 开始的 $limit 条数据
     * @param Integer $limit
     * @param Integer $offset 默认值 0
     * @return Queryable 查询对象
     */
    public function take($limit, $offset = 0)
    {
        // 如果查询字段已经指定，报错
        if(!empty($this->preSelectSQL['limit'])) {
            throw new Exception('There is a limit clause already!');
        }

        if($limit <= 0) {
            $this->preSelectSQL['limit'] = '';
            return $this;
        }

        $limitStr = 'LIMIT ' . $limit;

        // offset 为take的起始，不可小于0
        $offset = ($offset < 0) ? 0 : $offset;
        if($offset > 0) {
            $limitStr .= ' OFFSET ' . $offset;
        }

        $this->preSelectSQL['limit'] = $limitStr;
        return $this;
    }

    /**
     * 表链接
     * @param Model $model
     * @param String $alias
     * @param String $way 链接方式 LEFT/RIGHT/INNER/OUTER 不分大小写
     * @return JoinQueryable
     */
    public function join($model, $alias, $way = JoinQueryable::LEFT_JOIN) 
    {
        return new JoinQueryable($this, $model, $alias, $way);
    }
    public function leftJoin($model, $alias)
    {
        return $this->join($model, $alias, JoinQueryable::LEFT_JOIN);
    }
    public function rightJoin($model, $alias)
    {
        return $this->join($model, $alias, JoinQueryable::RIGHT_JOIN);
    }
    public function innerJoin($model, $alias)
    {
        return $this->join($model, $alias, JoinQueryable::INNER_JOIN);
    }

    /**
     * 附加join要查询的字段
     */
    public function appendJoinFields($fields)
    {
        $this->preSelectSQL['fields'] .= ', '. $fields;
    }
    /**
     * 设置join语句
     * @param String $joinSQL  
     */ 
    public function setJoin($joinSQL)
    {
        if(!is_array($this->preSelectSQL['join'])) {
            $this->preSelectSQL['join'] = array();
        }

        array_push($this->preSelectSQL['join'], $joinSQL);
    }

    /**
     * 生成 查询SQL
     */
    private function generateSelectSQL()
    {
        $sql = array('SELECT');

        if($this->preSelectSQL['distinct']) {
            array_push($sql, 'DISTINCT');
        }

        array_push($sql, $this->preSelectSQL['fields']);
        array_push($sql, 'FROM ');
        array_push($sql, $this->preSelectSQL['table']);

        if(!empty($this->preSelectSQL['join'])) {
            array_push($sql, implode(' ', $this->preSelectSQL['join']));
        }

        if(!empty($this->preSelectSQL['where'])) {
            array_push($sql, 'WHERE (');
            array_push($sql, implode(' ) AND (', $this->preSelectSQL['where']));
            array_push($sql, ')');
        }

        if(!empty($this->preSelectSQL['groupBy'])) {
            array_push($sql, 'GROUP BY');
            array_push($sql, $this->preSelectSQL['groupBy']);

            if(!empty($this->preSelectSQL['having'])) {
                array_push($sql, 'HAVING (');
                array_push($sql, implode(' ) AND (', $this->preSelectSQL['having']));
                array_push($sql, ')');
            }
        }

        if(!empty($this->preSelectSQL['orderBy'])) {
            array_push($sql, 'ORDER BY');
            array_push($sql, implode(', ', $this->preSelectSQL['orderBy']));
        }

        if(!empty($this->preSelectSQL['limit'])) {
            array_push($sql, $this->preSelectSQL['limit']);
        }

        return implode(' ', $sql);
    }

    /**
     * 执行查询，返回数据集
     * @return Array
     */
    public function query()
    {
        $selectSQL = $this->generateSelectSQL();
        $stmt = $this->model->getConnection()->prepareSQL($selectSQL);
        $stmt->execute($this->preSelectBindingData);

        // 返回数据集合
        return $stmt->fetchAll(PDO::FETCH_NAMED);
    }

    /**
     * 取得第一条数据
     * @param String/Array fieldname
     * @return Array
     */
    public function first($fieldname = '*')
    {
        $selectSQL = ['SELECT'];
        if($fieldname == '*') {
            array_push($selectSQL, 'tmp.*');
        }
        else {
            if(is_array($fieldname)) {
                array_push($selectSQL, implode(', ', Common::aliasFields($fieldname, 'tmp')));
            }
            else {
                array_push($selectSQL, 'tmp.' . $fieldname);
            }
        }
        array_push($selectSQL, 'FROM (');
        array_push($selectSQL, $this->generateSelectSQL());
        array_push($selectSQL, ') AS tmp LIMIT 1');
 
        $stmt = $this->model->getConnection()->prepareSQL(implode(' ', $selectSQL));
        $stmt->execute($this->preSelectBindingData);
        
        if(is_string($fieldname) && $fieldname != '*') {
            return $stmt->fetchColumn(0);
        }
        // 返回第一条记录
        return $stmt->fetch(PDO::FETCH_NAMED);
    }

    /**
     * 获取全部数据条数
     * @return Integer
     */
    public function total()
    {
        $pendingSelectSQL = $this->generateSelectSQL();
        // 如果查询语句中有 LIMIT 关键字，从LIMIT开始往后的内容删除
        if($idx = stripos($pendingSelectSQL, 'LIMIT')) {
            $pendingSelectSQL = substr($pendingSelectSQL, 0, $idx - 1);
        }
        $selectSQL = 'SELECT count(0) AS ROWCOUNT FROM (' . $pendingSelectSQL . ') as temp';

        $stmt = $this->model->getConnection()->prepareSQL($selectSQL);
        $stmt->execute($this->preSelectBindingData);

        return $stmt->fetchColumn(0) ?? 0;
    }
}