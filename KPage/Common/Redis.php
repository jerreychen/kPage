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

use KuaKee\Config;

final class Redis
{
    static private $_instance;
    public $redis;

    private function __construct()
    {
        $redisHost = Config::get('cache.redis.host');
        $redisPort = Config::get('cache.redis.port');
        $redisPwd = Config::get('cache.redis.pwd', '');
        
        $this->redis = new \Redis();
        $this->redis->connect($redisHost, $redisPort);
 
        if(!empty($redisPwd)) { 
            $this->redis->auth($redisPwd);
        }
    }

    static public function getInstance()
    {
        if(!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 返回连接状态
     * @return Integer
     */
    public function ping()
    {
        return $this->redis->ping();
    }

    /**
     * 设置一个key-value，当$unique为true，$key重复则设置失败，返回false
     * @param String $key
     * @param String $value
     * @param Boolean $unique 
     * @return Boolean
     */
    public function set($key, $value, $unique = false)
    {
        if($unique) {
            return $this->redis->setNx($key, $value);
        }

        return $this->redis->set($key, $value);
    }

    /**
     * 设置$key的超时时间
     * @param String $key
     * @param Integer $ttl 
     * @param Boolean $millisecond 是否单位是毫秒，false（秒）true（毫秒）
     * @return Boolean
     */
    public function setExpires($key, $ttl, $millisecond = false)
    {
        if($millisecond) {
            return $this->redis->pexpire($key, $ttl);
        }
        return $this->redis->expire($key, $ttl);
    }
    /**
     * 设置$key的结束时间
     * @param String $key 
     * @param Integer $ttl 
     * @param Boolean $millisecond 是否单位是毫秒，false（秒）true（毫秒）
     * @return Boolean
     */
    public function setExpireAt($key, $expireAt, $millisecond = false)
    {
        if($millisecond) {
            return $this->redis->pexpireAt($key, $expireAt);
        }
        return $this->redis->expireAt($key, $expireAt);
    }

    /**
     * 取值
     * @param String $key
     * @return String
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * 删除一个建
     * @param String $key
     * @return Boolean
     */
    public function del($key)
    {
        return $this->redis->del($key);
    }

    /**
     * 增加数值
     * @param String $key
     * @param Number $value
     * @param Boolean $isFloat
     * @return Boolean
     */
    public function incrBy($key, $value = 1, $isFloat = false)
    {
        if($isFloat) {
            return $this->redis->incrByFloat($key, $value);
        }

        return $this->redis->incrBy($key, $value);
    }

    /**
     * 设置多个key-value的值，当$unique为true，只有所有的数据都成功才会返回成功，否则放回失败
     * @param Array $keyValues
     * @param Boolean $unique
     * @return Array
     */
    public function setMulti($keyValues, $unique = false)
    {
        if($unique) {
            return $this->redis->mSetNx($keyValues);
        }
        return $this->redis->mSet($keyValues);
    }

    /**
     * 获取多个key的值
     * @param Array $keys 
     * @return Array
     */
    public function getMulti($keys)
    {
        return $this->redis->mGet($keys);
    }

    /**
     * 随机获取一个key的值 
     * @return String
     */
    public function getRandom()
    {
        $key = $this->redis->randomKey();
        return $this->redis->get($key);
    }

    /**
     * 重命名key，当$unique为true，$key重复则重命名失败，返回false
     * @param String $key
     * @param String $newKey
     * @param Boolean $unique
     * @return Boolean
     */
    public function renameKey($key, $newKey, $unique = false)
    {
        if($unique) {
            return $this->redis->renameNx($key, $newKey);
        }
        return $this->redis->rename($key, $newKey);
    }

    /**
     * 修改$key的值并返回旧的值
     * @param String $key
     * @param String $value
     * @return String
     */
    public function updateValue($key, $value)
    {
        return $this->redis->getSet($key, $value);
    }
    

    /**
     * 根据通配符查找key
     * @param String $wildcard
     * @return Array
     */
    public function keys($wildcard = '*')
    {
        return $redis->keys($name);
    }

    #region List列表数据

    /**
     * 将值设置到List中指定的索引号
     * @param String $key
     * @param String $value
     * @param Integer $index 
     * @return Boolean
     */
    public function lSet($key, $value, $index)
    {
        return $this->redis->lSet($key, $index, $value);
    }

    /**
     * 从头开始删除List中值为$value的数据，$count 为删除的个数
     * @param String $key
     * @param String $value
     * @param Integer $count 0，表示所有
     * @return Mixed
     */
    public function lDel($key, $value, $count = 1)
    {
        return $this->redis->lRem($key, $value, $value);
    }

    /**
     * 取到List中指定index的值
     * @param String $key
     * @param Integer $index 
     * @return Mixed
     */
    public function lGet($key, $index)
    {
        return $this->redis->lIndex($key, $index);
    }

    /**
     * 存储数据到列表中，先入先出（类似于Array的push)
     * @param String $key
     * @param String $value,
     * @param Boolean $stackTop // 是否插入堆栈顶部插入
     */
    public function lPush($key, $value, $stackTop = false)
    {
        if(!$stackTop) {
            $this->redis->lPush($key, $value);
        }
        else {
            $this->redis->rPush($key, $value);
        }
    }
    
    /**
     * 从列表数据中弹出一条数据，弹出数据后，从列表删除
     * @param String $key
     * @param Boolean $stackTop // 是否从堆栈顶部弹出
     * @return String
     */
    public function lPop($key, $stackTop = false)
    {
        if(!$stackTop) {
            return $this->redis->rPop($key);
        }
        
        return $this->redis->lPop($key);
    }

    /**
     * 从列表中获取数据
     * @param Integer @start 开始索引号
     * @param Integer @end 结束索引号
     * @return Array
     */
    public function lRange($start, $end)
    {
        return $this->redis->lRange($start, $end);
    }

    /**
     * 获取列表长度
     * @param String $key
     * @return Integer
     */
    public function lLength($key)
    {
        return $this->redis->lLen($key);
    }

    #endregion

    #region hash表

    /**
     * 给hash表中某个key设置value
     * 当 $unique = false：如果没有则设置成功,返回1,如果存在会替换原有的值,返回0,失败返回0
     * 当 $unique = true：如果$key重复会返回 false
     * @param String $hashID
     * @param String $key
     * @param String $value
     * @param Boolean $unique
     * @return Mixed
     */
    public function hSet($hashID, $key, $value, $unique = false)
    {
        if($unique) {
            return $this->redis->hSetNx($hashID, $key, $value);
        }
        return $this->redis->hSet($hashID, $key, $value);
    }

    /**
     * 获取hash中某个key的值
     * @param String $hashID
     * @param String $key
     * @return Mixed
     */
    public function hGet($hashID, $key) 
    {
        return $this->redis->hGet($hashID, $key);
    }

    /**
     * 判断$key是否存在
     * @param String $hashID
     * @param String $key
     * @return Boolean
     */
    public function hExists($hashID, $key)
    {
        return $this->redis->hExists($hashID, $key);
    }

    /**
     * 获取一个hash中所有的key和value 顺序是随机的
     * @param String $hashID
     * @return Array
     */
    public function hAll($hashID) {
        return $this->redis->hGetAll($hashID);
    }
    
    /**
     * 获取hash中key的数量
     * @param String @hashID
     * @return Integer
     */
    public function hLength($hashID) 
    {
        return $this->redis->hLen($hashID);
    }

    /**
     * 获取hash中所有的keys
     * @param String @hashID
     * @return Array
     */
    public function hKeys($hashID) 
    {
        return $this->redis->hKeys($hashID);
    }

    /**
     * 获取hash中所有的值 顺序是随机的
     * @param String @hashID
     * @return Array
     */
    public function hValues($hashID)
    {
        return $this->redis->hVals($hashID);
    }

    /**
     * 删除hash中一个key 如果表不存在或key不存在则返回false
     * @param String @hashID
     * @param Integer
     */
    public function hDel($hashID, $key)
    {
        return $this->redis->hDel($hashID, $key);
    }

    /**
     * 增加数值
     * @param String @hashID
     * @param String $key
     * @param Number $value
     * @param Boolean $isFloat
     * @return Boolean
     */
    public function hIncrBy($hashID, $key, $value = 1, $isFloat = false)
    {
        if($isFloat) {
            return $this->redis->hIncrByFloat($hashID, $key, $value);
        }

        return $this->redis->hIncrBy($hashID, $key, $value);
    }

    /**
     * 插入多条key-value数据
     * @param String $hashID
     * @param Array $keyValues
     * @return Boolean
     */
    public function hSetMulti($hashID, $keyValues)
    {
        $this->redis->hMSet($hashID, $keyValues);
    }

    /**
     * 获取多条keys的数据
     * @param String $hashID
     * @param Array $keys
     * @return Array
     */
    public function hGetMulti($hashID, $keys)
    {
        return $this->redis->hMGet($hashID, $keys);
    }

    #endregion

    #region 无序集合

    /**
     * 添加一个元素到无序集合中，重复元素添加失败
     * @param String $key
     * @param String $value
     * @return Integer
     */
    public function sAdd($key, $value)
    {
        return $this->redis->sAdd($key, $value);
    }

    /**
     * 查看无序集合中所有的元素
     * @param String $key
     * @return Array
     */
    public function sAll($key)
    {
        return $this->redis->sMembers($key);
    }

    /**
     * 删除无序集合中的value
     * @param String $key
     * @param String $value
     * @return Integer
     */
    public function sDel($key, $value)
    {
        return $this->redis->sRem($key, $value);
    }

    /**
     * 判断元素是否是无序集合中的成员
     * @param String $key
     * @param String $value
     * @return Boolean
     */
    public function sExists($key, $value)
    {
        return $this->redis->sIsMember($key, $value);
    }

    /**
     * 查看无序集合中成员的数量
     * @param String $key
     * @return Integer
     */
    public function sLength($key)
    {
        return $this->redis->sCard($key);
    }

    /**
     * 弹出并返回集合中的一个随机元素(返回被弹出的元素)
     * @param String $key
     * @return String
     */
    public function sPop($key)
    {
        return $this->redis->sPop($key);
    }

    /**
     * 返回两个集合的交集，存储到 storeKey 集合中（如果不为空）
     * @param String $key1
     * @param String $key2
     * @param String $storeKey 执行交集操作 并结果放到一个集合中
     * @return Array
     */
    public function sInter($key1, $key2, $storeKey = '')
    {
        if(empty($storeKey)) {
            return $this->redis->sInter($key1, $key2);
        }
        
        $this->redis->sInterStore($storeKey, $key1, $key2); 
    }

    /**
     * 返回两个集合的并集，存储到 storeKey 集合中（如果不为空）
     * @param String $key1
     * @param String $key2
     * @param String $storeKey 执行并集操作 并结果放到一个集合中
     * @return Array
     */
    public function sUnion($key1, $key2, $storeKey = '')
    {
        if(empty($storeKey)) {
            return $this->redis->sUnion($key1, $key2);
        }
        
        $this->redis->sUnionStore($storeKey, $key1, $key2);
    }

    /**
     * 返回两个集合的差集，存储到 storeKey 集合中（如果不为空）
     * @param String $key1
     * @param String $key2
     * @param String $storeKey 执行差集操作 并结果放到一个集合中
     * @return Array
     */
    public function sDiff($key1, $key2, $storeKey = '')
    {
        if(empty($storeKey)) {
            return $this->redis->sDiff($key1, $key2);
        }
        
        $this->redis->sDiffStore($storeKey, $key1, $key2);
    }

    #endregion

    #region 有序集合

    /**
     * 有序集合，添加元素 分数
     * @param String $key
     * @param String $value
     * @param Number @score
     * @return Integer
     */
    public function zAdd($key, $value, $score)
    {
        return $this->redis->zAdd($key, $score, $value);
    }

    /**
     * 有序集合中指定值的socre增加
     * @param String $key
     * @param String $value
     * @param Number @score
     * @return Integer
     */
    public function zIncrBy($key, $value, $score)
    {
         $this->redis->zIncrBy($key, $score, $value);
    }

    /**
     * 返回集合中的指定元素
     * @param String $key
     * @param Integer $start
     * @param Integer $end
     * @param Boolean $withScore // 带上score值
     * @return Array
     */
    public function zRange($key, $start, $end, $withScore = false)
    {
        if($withScore) {
            return $this->redis->zRange($key, $start, $end, true);
        }

        return $this->redis->zRange($key, $start, $end);
    }

    /**
     * 返回集合中的指定元素（倒序）
     * @param String $key
     * @param Integer $start
     * @param Integer $end
     * @param Boolean $withScore // 带上score值
     * @return Array
     */
    public function zRevRange($key, $start, $end, $withScore = false)
    {
        if($withScore) {
            return $this->redis->zRevRange($key, $start, $end, true);
        }

        return $this->redis->zRevRange($key, $start, $end);
    }

    /**
     * 返回存储的个数
     * @param String $key 
     * @return Number
     */
    public function zLength($key)
    {
        return $this->redis->zCard($key);
    }

    /**
     * 返回元素的score值
     * @param String $key 
     * @param String $value
     * @return Number
     */
    public function zScore($key, $value)
    {
        return $this->redis->zScore($key, $value);
    }

    /**
     * 删除指定成员
     * @param String $key 
     * @param String $value
     * @return Number
     */
    public function zDel($key, $value)
    {
        return $this->redis->zRem($key, $value);
    }

    /**
     * 返回集合中介于min和max之间的值的个数
     * @param String $key 
     * @param Number $scoreMin
     * @param Number $scoreMax
     * @return Integer
     */
    public function zCount($key, $scoreMin, $scoreMax) 
    {
        return $this->redis->zCount($key, $scoreMin, $scoreMax);
    }

    /**
     * 返回有序集合中score介于min和max之间的数据
     * @param String $key 
     * @param Number $scoreMin
     * @param Number $scoreMax
     * @param Boolean $withScore
     * @return Integer
     */
    public function zRangeByScore($key, $scoreMin, $scoreMax, $withScore = false)
    {
        if($withScore) {
            return $this->redis->zRangeByScore($key, $scoreMin, $scoreMax, ['withscores' => true]);
        }

        return $this->redis->zRangeByScore($key, $scoreMin, $scoreMax);
    }

    /**
     * 返回有序集合中score介于min和max之间的数据，倒序
     * @param String $key 
     * @param Number $scoreMin
     * @param Number $scoreMax
     * @param Boolean $withScore
     * @return Integer
     */
    public function zRevRangeByScore($key, $scoreMin, $scoreMax, $withScore = false)
    {
        if($withScore) {
            return $this->redis->zRevRangeByScore($key, $scoreMin, $scoreMax, ['withscores' => true]);
        }

        return $this->redis->zRevRangeByScore($key, $scoreMin, $scoreMax);
    }

    /**
     * 移除score值介于min和max之间的元素
     * @param String $key 
     * @param Number $scoreMin
     * @param Number $scoreMax
     * @return Integer
     */
    public function zDelByScore($key, $scoreMin, $scoreMax)
    {
        return $this->redis->zRemRangeByScore($key, $scoreMin, $scoreMax);
    }

    #endregion
}