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

final class JoinQueryable
{
    const LEFT_JOIN = 'LEFT';
    const RIGHT_JOIN = 'RIGHT';
    const INNER_JOIN = 'INNER';

    private $queryable; 

    private $tableAlias;

    private $preJoinSQL = [];

    /**
     * 构造函数
     * @param Queryable $queryable
     * @return JoinQueryable
     */
    public function __construct($queryable, $model, $alias, $way)
    {
        $way = strtoupper($way);
        
        // 如果不是 left/right/inner 等join类型，返回错误
        if(!in_array($way, [self::LEFT_JOIN, self::RIGHT_JOIN, self::INNER_JOIN])) {
            exit('Invalid join type!');
        }

        $this->queryable = $queryable;
        $this->tableAlias = $alias;
        
        array_push($this->preJoinSQL, $way);
        array_push($this->preJoinSQL, 'JOIN');
        array_push($this->preJoinSQL, $model->getTableName());
        array_push($this->preJoinSQL, 'AS');
        array_push($this->preJoinSQL, $alias);

        return $this;
    }

    /**
     * join 条件
     * @param String/Array join 查询的条件
     * @return Queryable
     */
    public function on($onClause)
    {
        array_push($this->preJoinSQL, 'ON');

        if(is_array($onClause)) { 
            array_push($this->preJoinSQL, '(');
            array_push($this->preJoinSQL, implode(' AND ', array_map(function($clause) {
                // 第二维如果是数组，组合成 OR 查询
                if(is_array($clause) && count($clause) > 0) {
                    return '(' . implode(' OR ', $clause) . ')';
                }

                return $clause;
            }, $onClause)));
            array_push($this->preJoinSQL, ')');
        }
        else {
            array_push($this->preJoinSQL, $onClause);
        }

        $this->queryable->setJoin(implode(' ', $this->preJoinSQL));

        return $this->queryable;
    }

    /**
     * 查询字段选择
     * @param String $fields 查询字段，默认*
     * @return JoinQueryable
     */
    public function select($fields = null) 
    { 
        if(!empty($fields)) {
            $fieldsArr = Common::aliasFields(Common::getFields($fields), $this->tableAlias);
            $this->queryable->appendJoinFields(implode(', ', $fieldsArr)); 
        }

        return $this;
    }
}