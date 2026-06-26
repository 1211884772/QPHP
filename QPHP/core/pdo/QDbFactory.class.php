<?php
namespace QPHP\core\pdo;


use Exception;
use QPHP\core\pdo\mysql\QDbMysql;
use QPHP\core\pdo\oracle\QDbOracle;

class QDbFactory
{
    private static  $qdb=[];

    /**
     * @throws Exception
     */
    static public function getDb($dbKey='mysql_0', $dbType='mysql'):QDbMysql|QDbOracle
    {

        if(!empty($dbType)) {
            $model_class = 'QPHP\core\pdo'.'\\'.strtolower($dbType).'\\QDb'.ucfirst($dbType);
                if(isset(self::$qdb[$dbKey])&&self::$qdb[$dbKey] instanceof $model_class){
                    return self::$qdb[$dbKey];
                }else{
                    //echo __NAMESPACE__;
                    if(class_exists($model_class)) {
                        self::$qdb[$dbKey] = new $model_class($dbKey);
                    }
                }
            return self::$qdb[$dbKey];
        }else{
            throw new Exception("The database type is empty");
        }
    }

}
