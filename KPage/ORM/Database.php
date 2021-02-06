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

use \KuaKee\App;
use \KuaKee\Config;
use PDO;
use PDOException;

final class Database
{
    const SQLITE = 'sqlite';
    const MYSQL = 'mysql';
 
    /** 静态变量 */
    static private $instance = null;

    /** 数据库连接对象 */
    private $connection = null;

    protected $transactionCounter = 0;

    private function __construct($dns, $username, $password) { 
        try {
            $this->connection = new PDO($dns, $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $pe) {
            exit($pe->getMessage());
        }
    }
 
    /**
     * 创建数据库链接实例 
     * @return Database 当前实例
     */
    static public function create()
    {
        if(!(self::$instance instanceof self)) {

            $dns = '';
            $userid = '';
            $password = '';
            $db_type = Config::get('database.type', 'sqlite');
            
            // 判断数据库类型，支持SQLITE，MYSQL
            switch(strtolower($db_type)) {
                case self::MYSQL:
                    $dns = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
                                    Config::get('database.'.self::MYSQL.'.host'),
                                    Config::get('database.'.self::MYSQL.'.port'),
                                    Config::get('database.'.self::MYSQL.'.db_name'),
                                    Config::get('database.'.self::MYSQL.'.charset'));

                    $userid = Config::get('database.'.self::MYSQL.'.uid');
                    $password = Config::get('database.'.self::MYSQL.'.pwd');
                break;
                case self::SQLITE:
                    $db_dir = Config::get('database.'.self::SQLITE.'.dir');
                    $db_filename = Config::get('database.'.self::SQLITE.'.filename');
                    $sqliteFilePath = App::$rootPath . '/' . $db_dir . '/' . $db_filename;
                    if(!file_exists($sqliteFilePath)) {
                        exit('SQLITE database cannot be found!');
                    }

                    $dns = sprintf('sqlite:%s', $sqliteFilePath);
 
                    $password = Config::get('database.'.self::SQLITE.'.pwd');
                break;
            }

            // 如果 driver 不是 SQLITE/MYSQL 
            if(empty($dns)) {
                exit('DNS unavailable!');
            }
            
            // 创建实例
            self::$instance = new self($dns, $userid, $password);
        }
 
        return self::$instance;
    }

    /**
     * 执行sql语句，返回受影响的条数
     * @param String $sql
     * @return Integer
     */
    public function exec($sql)
    {
        return $this->connection->exec($sql);
    }

    /**
     * prepare the statement for executing
     * @param String $sql
     * @return PDOStatement
     */
    public function prepareSQL($sql)
    {
        $stmt = $this->connection->prepare($sql);
        if($stmt === false) {
            exit('The database server cannot successfully prepare the statement.');
        }

        return $stmt;
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     * @param String $sql
     * @return PDOStatement
     */
    public function querySQL($sql) 
    {
        $stmt = $this->connection->query($sql);
        if($stmt === false) {
            exit('The database server cannot successfully execute the sql.');
        }

        return $stmt;
    }

    public function beginTransaction()
    {
        if (!$this->transactionCounter++) {
            return $this->connection->beginTransaction();
        }
        return $this->transactionCounter >= 0;
    }

    public function commit()
    {
        if (!--$this->transactionCounter) {
            return $this->connection->commit();
        }
        return $this->transactionCounter >= 0;
    }

    public function rollback()
    {
        if (--$this->transactionCounter) { 
            $this->transactionCounter = 0;
            return true;
        }
        $this->transactionCounter = 0;
        return $this->connection->rollback();
    }

    /**
     * 返回最后一次插入的记录ID
     */
    public function lastInsertID($name = null)
    {
        return $this->connection->lastInsertId($name);
    }

    /**
     * 返回错误信息
     */
    public function getError()
    {
        return array(
            'code'  => $this->connection->errorCode(),
            'info'  => $this->connection->errorInfo()
        );
    }
}