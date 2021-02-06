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

use Exception;

abstract class Model
{
    /**
     * db connection
     */
    private $conn;

    /** 
     * 表名称，必须重写（override）
     * @var String
     */
    protected $tableName;

    /**
     * 设置主键，必须重写（override）
     * 如果有多个主键用英文状态的逗号（,)进行分割，或者使用字符串数组
     * @var String/Array
     */
    protected $primaryKey;

    /**
     * 软删除（数据保留，标记删除）字段（默认值：deleted，字段名可更改）
     * 字段值：“0”（非删除）、“1”（删除）
     * 若要设置实体删除（数据删除），设置该值为：false
     * @var String/Boolean
     */
    protected $softDelete;

    public function __construct()
    {
        $this->conn = Database::create();
    }

    /**
     * 返回 connection 对象
     * @return Connection
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * 返回 tableName
     * @return String
     */
    public function getTableName() 
    {
        return $this->tableName;
    }

    /**
     * 返回 Primary Key
     * @return Array
     */
    public function getPrimaryKey()
    {
        if(is_array($this->primaryKey)) {
            return $this->primaryKey;
        }

        return array_map(function($field) {
            return trim($field);
        }, explode(',', $this->primaryKey));
    }

    /**
     * 返回软删除键名
     * @return String/Boolean
     */
    public function getSoftDeleteKey()
    {
        return $this->softDelete;
    }

    /**
     * 获取表内的所有字段
     * @return Array
     */
    public function getFields($nameOnly = true)
    {
        $sql = "PRAGMA table_info([$this->tableName]);";
        $ret = $this->conn->querySQL($sql);

        $fields = $ret->fetchAll();
        return array_map(function($item) use ($nameOnly) { 
            // row $item: 0: cid, 1:title, 2:data type, 3:not null, 4:default value, 5: primary key
            return $nameOnly ? $item[1] : ['name' => $item[1], 
                                           'type' => $item[2], 
                                           'not_null' => $item[3], 
                                           'default_value' => $item[4],
                                           'primary_key' => $item[5]];
        }, $fields);
    }

    /**
     * 创建数据表
     * @param Array/String $columns 数据表字段信息
     * @return Integer
     */
    public function createTable($columns)
    {
        if(!is_array($columns)) {
            $columns = [$columns];
        }

        $preExecSQL = ['CREATE TABLE'];
        array_push($preExecSQL, $this->tableName);
        array_push($preExecSQL, '('); 
        array_push($preExecSQL, implode(', ', $columns)); 
        array_push($preExecSQL, ')');
 
        try{
            $this->conn->exec(implode(' ', $preExecSQL));
        }
        catch(Exception $err) {
            exit($err->getMessage());
        }

        return true;
    }

    /**
     * 为表添加字段
     * @param Array/String $columns 数据表字段信息
     * @return Integer
     */
    public function addColumn($columns)
    {
        if(!is_array($columns)) {
            $columns = [$columns];
        }

        $tableName = $this->tableName;
 
        $preExecSQL = array_map(function($column) use ($tableName) {
            return 'ALTER TABLE '. $tableName .' ADD COLUMN '. $column;
        }, $columns);
         
        try{
            $this->conn->exec(implode('; ', $preExecSQL));
        }
        catch(Exception $err) {
            exit($err->getMessage());
        }

        return true;
    }
    
    /**
     * 查询
     * @param String $fields 查询字段；默认值 '*'，查询所有字段；也可使用英文状态下的（,）分隔的字符串
     * @param String $alias 表别名；默认值 null
     * @return Queryable
     */
    public function select($fields = '*', $alias = null)
    {
        return new Queryable($this, $fields, $alias);
    }
  
    /**
     * 获取指定ID的记录
     * @param Array $pk_value,主键的值，多个主键值以逗号分隔
     * @return Array
     */
    public function find($pk_value) 
    { 
        if(!is_array($pk_value)) {
            $pk_value = [$pk_value];
        }
        $primaryKeys = $this->getPrimaryKey();

        if(count($primaryKeys) != count($pk_value)) {
            exit('存在多个主键，值缺失！');
        }

        $whereClause = [];
        $data = [];
        $i = 0;
        foreach($primaryKeys as $pk) {
            array_push($whereClause, "$pk = :$pk");
            $data[":$pk"] = $pk_value[$i++];
        }
        return $this->select()->where(implode(' AND ', $whereClause), $data)->first();
    }

    /**
     * 执行查询，返回数据集
     * @return Array
     */
    public function query()
    {
        return $this->select()->query();
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
        return $this->select()->join($model, $alias, $way);
    }

    /**
     * 返回全部数据的条数
     * @return Integer
     */
    public function total()
    {
        return $this->select()->total();
    }

    /**
     * 插入记录
     * @param Array $records 需要保存的记录，如果多条记录为二维数组
     * @param String/Array $whereClause，字符串为查询SQL，一位数组为多个查询条件，二维数组时候，第一维数组之间 AND，第二维数字之间 OR
     * @param Array $dataBind
     */
    public function insert($records)
    {
        $insertSQL = ['INSERT INTO'];
        array_push($insertSQL, $this->tableName);
    
        // 是否二维数组
        $arr2D = is_array(array_values($records)[0]);
        // 获取键值
        $fields = array_keys($arr2D ? $records[0]: $records);

        array_push($insertSQL, '('. implode(', ', $fields) .')');
        array_push($insertSQL, 'VALUES');
        array_push($insertSQL, '('. implode(', ', array_map(function($f) {
            return ':'. $f;
        }, $fields)) . ')');

        $sql = implode(' ', $insertSQL);
        $stmt = $this->conn->prepareSQL($sql);

        $result = [];
        // 如果 $records 是二维数组，则表示插入多条数据
        if($arr2D) { 
            try{
                $this->conn->beginTransaction();

                foreach($records as $record) {
                    $data = Common::generateBindData($record);
                    $stmt->execute($data);
                    array_push($result, $this->conn->lastInsertID());
                }
                
                $this->conn->commit(); 
            }
            catch(Exception $ex) {
                $this->conn->rollback();
                // 回滚后 $result 清空
                $result = [];
            }
        }
        else {
            $data = Common::generateBindData($records);
            $stmt->execute($data);
            array_push($result, $this->conn->lastInsertID());
        }

        return $result;
    }

    /**
     * 更新记录
     * @param Array $record 需要保存的记录
     * @param String/Array $whereClause，字符串为查询SQL，一位数组为多个查询条件，二维数组时候，第一维数组之间 AND，第二维数字之间 OR
     * @param Array $dataBind
     */
    public function update($record, $whereClause = '', $dataBind = null)
    {
        $updateWherePrefix = 'UPD';
        $updateSQL = ['UPDATE'];
        array_push($updateSQL, $this->tableName);
        array_push($updateSQL, 'SET');
    
        // 获取键值
        $fields = array_keys($record);
        $arrFields = [];
        $arrFieldData = [];
        foreach($fields as $field) {
            array_push($arrFields, $field . '=:' . $field);
            $arrFieldData[':'. $field] = $record[$field];
        }
        array_push($updateSQL, implode(', ', $arrFields));

        // 如果存在 where 条件语句
        if(!empty($whereClause)) {
            array_push($updateSQL, "WHERE");
            array_push($updateSQL, Common::generateWhereClause($whereClause, $updateWherePrefix));
        }
        else {
            $dataBind = null; // 如果 whereClause为空，则数据强制置空
        }

        $sql = implode(' ', $updateSQL);
        
        $data = Common::generateBindData($dataBind, $updateWherePrefix);
        if(is_array($data)) {
            $data = array_merge($data, $arrFieldData);
        }
        else {
            $data = $arrFieldData;
        }

        $stmt = $this->conn->prepareSQL($sql);
        $stmt->execute($data);

        // 返回受影响数据的条数
        return $stmt->rowCount();
    }

    /**
     * 删除指定 $id 的数据
     * @param Array/Mixed $pk_value
     * @return Integer
     */
    public function del($pk_value) 
    { 
        if(!is_array($pk_value)) {
            $pk_value = [$pk_value];
        }
        $primaryKeys = $this->getPrimaryKey();

        if(count($primaryKeys) != count($pk_value)) {
            exit('存在多个主键，值缺失！');
        }

        $whereClause = [];
        $data = [];
        $i = 0;
        foreach($primaryKeys as $pk) {
            array_push($whereClause, "$pk = :$pk");
            $data[":$pk"] = $pk_value[$i++];
        }

        return $this->delete(implode(' AND ', $whereClause), $data);
    }

    /**
     * 删除记录
     * @param String/Array $whereClause 删除条件，查询条件字符串
     * @param Array $dataBind where 条件的绑定值 如 [':id' => 1, ':name' => 'name']
     * @return INT
     */
    public function delete($whereClause = '', $dataBind = null)
    {
        // 设置了 softDelete，执行软删除
        if($this->softDelete) {
            return $this->update([$this->softDelete => 1], $whereClause, $dataBind);
        }
        
        $delWherePrefix = 'DEL';
        $deleteSQL = ['DELETE FROM'];
        array_push($deleteSQL, $this->tableName);

        // 如果存在 where 条件语句
        if(!empty($whereClause)) {
            array_push($deleteSQL, "WHERE");
            array_push($deleteSQL, Common::generateWhereClause($whereClause, $delWherePrefix));
        }
        else {
            $dataBind = null; // 如果 whereClause为空，则数据强制置空
        }

        // 组合sql
        $sql = implode(' ', $deleteSQL);

        $data = Common::generateBindData($dataBind, $delWherePrefix);

        $stmt = $this->conn->prepareSQL($sql);
        $stmt->execute($data);

        // 返回受影响数据的条数
        return $stmt->rowCount();
    }
}