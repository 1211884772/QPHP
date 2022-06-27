<?php


class QDbFactory
{
    static private $qdb=[];

    static public function getDb($dbKey='mysql_0',$dbType='mysql')
    {
        if(!empty($dbType)) {
            $db_type = ucfirst($dbType);
            $model_class = "QDb".$db_type;
            try{
                if(isset(self::$qdb[$model_class])&&self::$qdb[$model_class] instanceof $model_class){
                    return self::$qdb[$model_class];
                }else{
                    if(class_exists($model_class)) {
                        self::$qdb[$model_class] = new $model_class($dbKey);
                    }
                }
            }catch (Exception $e){
                //throw $e;
                throw new Exception("【{$model_class}】Database type error");
            }

           /* if($dbType==='mysql'){
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
            }*/
            return self::$qdb[$model_class];
        }else{
            throw new Exception("The database type is empty");
        }
    }


}
