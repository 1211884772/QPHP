<?php


class QDbFactory
{
    static private $qdb=null;

    static public function getDb($dbKey='mysql_0',$dbType='mysql')
    {
        if(!empty($dbType)) {
            if($dbType==='mysql'){
                if(self::$qdb instanceof QDbMysql){
                    return self::$qdb;
                }else{
                    self::$qdb = new QDbMysql($dbKey);
                }
            }elseif ($dbType==='oracle') {
                if(self::$qdb instanceof QDbOracle){
                    return self::$qdb;
                }else{
                    self::$qdb = new QDbOracle($dbKey);
                }
            }else{
                throw new Exception("Database type error");
            }
            return self::$qdb;
        }else{
            throw new Exception("The database type is empty");
        }
    }


}
