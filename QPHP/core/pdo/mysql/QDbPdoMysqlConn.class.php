<?php
namespace QPHP\core\pdo\mysql;

use Exception;
use PDO;
use QPHP\core\pdo\intf\IPdoConn;

class QDbPdoMysqlConn implements IPdoConn
{
    //数据库类型
    private string $dbType = 'mysql';
    private static ?PDO $connectId = null;

    /**
     * +----------------------------------------------------------
     * 打开数据库连接
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @throws Exception
     */
    public function connect($MYSQL_HOST,$MYSQL_PORT,$MYSQL_DB,$MYSQL_USER,$MYSQL_PWD):PDO
    {
        // 1. 已有连接 && 数据库配置没变，先检测连接是否存活
        if (self::$connectId !== null) {
            try {
                // 心跳检测，验证长连接未被MySQL断开
                //self::$connectId->query("SELECT 1");
                return self::$connectId;
            } catch (PDOException $e) {
                // 连接失效，清空重建
                self::$connectId = null;
            }
        }
        $options = [
            PDO::ATTR_PERSISTENT => true,       // 核心：开启持久TCP长连接，消灭TIME_WAIT
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,// 关闭模拟预处理，提升性能防注入
            PDO::ATTR_TIMEOUT => 5,             // 连接超时放宽到5秒，减少连接失败
            //PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4", // 一次性设置字符集，不用额外执行SQL
        ];
        self::$connectId  = new PDO("mysql:host=".$MYSQL_HOST.":".$MYSQL_PORT.";dbname=".$MYSQL_DB, $MYSQL_USER, $MYSQL_PWD,$options);
        //$connectId->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //打开PDO错误提示
        if ($this->dbType == 'mysql'){
            $stmt=self::$connectId->query("SHOW VARIABLES LIKE 'character_set_connection'");
            $charset=$stmt->fetch(PDO::FETCH_ASSOC);
            self::$connectId->exec("set names {$charset['Value']}");
        }
        return self::$connectId;
    }
}
