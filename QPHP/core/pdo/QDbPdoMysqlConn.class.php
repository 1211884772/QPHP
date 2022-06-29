<?php


class QDbPdoMysqlConn implements IPdoConn
{
    //数据库类型
    private $dbType = 'mysql';

    /**
    +----------------------------------------------------------
     * 打开数据库连接
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     */
    public function connect($MYSQL_HOST,$MYSQL_PORT,$MYSQL_DB,$MYSQL_USER,$MYSQL_PWD) {
        try{
            $connectId = new PDO("mysql:host=".$MYSQL_HOST.":".$MYSQL_PORT.";dbname=".$MYSQL_DB."", $MYSQL_USER, $MYSQL_PWD);
            $connectId->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //打开PDO错误提示
            if ($this->dbType == 'mysql'){
                $connectId->exec("set names utf8");
            }
            //$dsn = $username = $password = $encode = null;
            if ($connectId == null) {
                throw new Exception("PDO CONNECT ERROR");
            }
            return $connectId;
        }catch (Exception $e){
            throw new Exception("PDO CONNECT ERROR:".$e->getMessage());
        }
    }
}
