<?php


namespace QPHP\core\pdo\mysql;


use Exception;
use PDO;
use QPHP\core\pdo\intf\IPdoPool;

class QDbPdoMysqlPool implements IPdoPool
{
    private static array $instance=array();
    private static int $num =1;//此数设为1,t=吞吐量最大
    private static int $total_num=10;
    //防止外部创建新的数据库连接类
    protected function __construct(){}

    static public function Connect($dbKey='mysql_0',$dbType="mysql"):PDO
    {
        if(count(self::$instance)>self::$total_num)
        {
            throw new Exception("Too many connections");
        }
        $total_num =0;
        if(isset(self::$instance[$dbKey])){
            foreach (self::$instance[$dbKey] as $key=>$val){
                if($val){
                    ++$total_num;
                }
            }
        }else{
            //连接类不够200，创建新类10个
            self::ConDB();
            //var_dump(self::$instance);
            foreach (self::$instance[$dbKey] as $key=>$val){
                if($val){
                    ++$total_num;
                }
            }
        }
        if($total_num>0){
            //随机数保证数据库连接均衡
            $i=rand(0,$total_num-1);
            return self::$instance[$dbKey][$i];
        }else{
            self::ConDB();
            foreach (self::$instance[$dbKey] as $key=>$val){
                if($val){
                    ++$total_num;
                }
            }
            //随机数保证数据库连接均衡
            $i=rand(0,$total_num-1);
            return self::$instance[$dbKey][$i];
        }
    }

    /**
     * @throws Exception
     */
    static private function ConDB():void
    {
        self::instanceQDbPdoConn();
    }

    /**
     * 初始话链接数据库
     * @throws Exception
     */
    static private function instanceQDbPdoConn():void
    {
        $mysql_conn = new QDbPdoMysqlConn();
        foreach (MYSQL_POOL as $key => $val) {
            //$MYSQL_HOST,$MYSQL_PORT,$MYSQL_DB,$MYSQL_USER,$MYSQL_PWD
            //连接类不够100，创建新类
            while (true) {
                if (!isset(self::$instance[$key]) || count(self::$instance[$key]) < self::$num) {
                    self::$instance[$key][] = $mysql_conn->connect($val['MYSQL_HOST'], $val['MYSQL_PORT'], $val['MYSQL_DB'], $val['MYSQL_USER'], $val['MYSQL_PWD']);
                } else {
                    foreach (self::$instance[$key] as &$v){
                        if(!$v){
                            self::$instance[$key][] = $mysql_conn->connect($val['MYSQL_HOST'], $val['MYSQL_PORT'], $val['MYSQL_DB'], $val['MYSQL_USER'], $val['MYSQL_PWD']);
                        }
                    }
                    break;
                }
            }
        }
    }

}
