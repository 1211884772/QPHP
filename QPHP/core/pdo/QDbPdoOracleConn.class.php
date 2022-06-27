<?php


class QDbPdoOracleConn implements IPdoConn
{
    //数据库类型
    private $dbType = 'oracle';

    /**
    +----------------------------------------------------------
     * 打开数据库连接
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     */
    public function connect($ORACLE_HOST,$ORACLE_PORT,$ORACLE_DB,$ORACLE_USER,$ORACLE_PWD) {
        try {
            $tns = "
(DESCRIPTION =
    (ADDRESS_LIST =
          (ADDRESS = (PROTOCOL = TCP)(HOST = {$ORACLE_HOST})(PORT = {$ORACLE_PORT})))
          (CONNECT_DATA =(SERVICE_NAME = ORCL)
     )
)";


            $db      = "oci:dbname=";//连接字符串
            $connectId = new PDO($db.$tns.';charset=UTF8',$ORACLE_USER,$ORACLE_PWD,array(PDO::ATTR_PERSISTENT => TRUE));// 注意，这一个必须写
            $connectId->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //打开PDO错误提示
            //$dsn = $username = $password = $encode = null;
            if ($connectId == null) {
                throw new Exception("PDO CONNECT ERROR");
            }
            return $connectId;
        } catch(PDOException $e){
            throw new Exception("PDO CONNECT ERROR:".$e->getMessage());
        }


    }
}
