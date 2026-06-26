<?php
namespace admin\Model;

use QPHP\core\container\Container;
use QPHP\core\func\Func;
use QPHP\core\model\Model;

/**
 * Class UserModel类
 */
class UserModel extends Model
{
    protected string $table='mm_user';//数据表
    protected string $key='id';//主键
    protected string $dbType='mysql';//数据库类型

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 分布式数据库mysql事务测试
     * @return bool
     */
    public function getXaTransactionData(): bool
    {

        $this->getLastSql();
        echo 77;
        $xid=Func::xId();
        echo $xid;
        //die();
//        //try {
//            $this->Db('mysql_0')->xaStartTrans($xid);
//
//            $sql1="insert into mm_user (username,age,address,pwd) values ('mumuww8',18,'西安','123456')";
//            $res_1=  $this->Db('mysql_0')->executeSql("insert",$sql1);
//
//            var_dump($res_1);
//
//            $this->Db('mysql_1')->xaStartTrans($xid);
//            $sql2="insert into mm_user (username,age,address,pwd) values ('mumuww8',18,'西安','123456')";
//            $res_2 = $this->Db('mysql_1')->executeSql("insert",$sql2);
//
//            var_dump($res_2);
//        $this->Db('mysql_0')->xaPrepare($xid);
//        $this->Db('mysql_1')->xaPrepare($xid);
//
//           // echo 444;
//
//       // }catch (\Exception $e){
//        if($res_1&&$res_2){
//            $this->Db('mysql_0')->xaCommit($xid);
//            $this->Db('mysql_1')->xaCommit($xid);
//        }else{
//             $this->Db('mysql_0')->xaRollback($xid);
//             $this->Db('mysql_1')->xaRollback($xid);
//        }
//        return true;
//           // echo 555;
//           // return false;
//        //}

    }

    /**
     * 单机数据库mysql事务测试
     * @return bool
     */
    public function getTransactionData(): bool
    {
        echo 66;
       // $this->getLastSql();
       // $this->Db('mysql_0')->startTrans();
        $sql1="insert into mm_user (username,age,address,pwd) values ('mumuww5',18,'西安','123456')";
        $res_1=  $this->Db('mysql_0')->executeSql("insert",$sql1);
        var_dump($res_1);
        if(!$res_1){
            echo 222;
            //$this->Db('mysql_0')->rollback();
            return false;
        }
        $sql2="insert into mm_user_info (user_id,birthday,name,info) values (1,599068800,'mumuww5','wangwei')";
        $res_2 = $this->Db('mysql_0')->executeSql("insert",$sql2);
        var_dump($res_2);
        if(!$res_2) {
            //$this->Db('mysql_0')->rollback();
            echo 333;
            return false;
        }
       // $this->Db('mysql_0')->commit();
        echo 444;
        return true;
    }

    public function getUser(){

        //$sql0="INSERT INTO q_user_01(id,name,pwd,create_time,updat_time) select * from q_user_01";

        //$data_2=$this->Db('mysql_2')->executeSql("query",$sql0);
       // select id,title from collect where id>=(select id from collect order by id limit 90000,1) limit 10;

        $data = [];
        //第二页 id<35 //分页由前端做，一次性返回多条数据
        $data_1 = $this->getUser01();
        $data_2 = $this->getUser02();
        $data_3 = $this->getUser03();
        $data = array_merge($data_1,$data_2,$data_3);
        $sort_c = array_column($data,'create_time');
        $sort_u = array_column($data,'updat_time');
        array_multisort($sort_c, SORT_DESC,SORT_NUMERIC,$sort_u,SORT_DESC,SORT_NUMERIC,$data);

        return $data;
    }


    public function getUser00(){
        //return array("1","2","3");
        //第二页 id<35 //分页由前端做，一次性返回多条数据

        $sql1="select * from mm_user where id<100  ORDER BY id desc limit 5";
       // $this->getLastSql();
        $data_1 = $this->Db('mysql_0')->executeSql("getRows",$sql1);

        return $data_1;
    }
    public function getUser01(){
        //return array("1","2","3");
        //第二页 id<35 //分页由前端做，一次性返回多条数据

        $sql1="select * from ((select * from q_user_01 where id<1162922  ORDER BY id desc limit 10)UNION ALL (select * from q_user_02 where id<1162922  ORDER BY id desc limit 10)UNION ALL (select * from q_user_03 where id<1162922  ORDER BY id desc limit 10)UNION ALL (select * from q_user_04 where id<1162922  ORDER BY id desc limit 10)UNION ALL (select * from q_user_05 where id<1162922  ORDER BY id desc limit 10)UNION ALL (select * from q_user_06 where id<1162922  ORDER BY id desc limit 10)UNION ALL (select * from q_user_07 where id<1162922  ORDER BY id desc limit 10)UNION ALL (select * from q_user_08 where id<1162922  ORDER BY id desc limit 10)UNION ALL (select * from q_user_09 where id<1162922  ORDER BY id desc limit 10)UNION ALL (select * from q_user_10 where id<1162922 ORDER BY id desc limit 10)) as u
where u.id<1162922 ORDER BY u.create_time desc limit 10";
        //$this->getLastSql();
        $data_1 = $this->Db('mysql_1')->executeSql("getRows",$sql1);

        return $data_1;
    }
    public function getUser02(){
       //return array("4","5","6");
        $sql2="select * from ((select * from q_user_01 )UNION ALL (select * from q_user_02)) as u
where u.id<35 ORDER BY u.create_time desc limit 10";
        $data_2=$this->Db('mysql_2')->executeSql("getRows",$sql2);

        return $data_2;
    }
    public function getUser03(){
        //return array("7","8","9");
        $sql3="select * from ((select * from q_user_01 )UNION ALL (select * from q_user_02)) as u
where u.id<35 ORDER BY u.create_time desc limit 10";
        $data_3=$this->Db('mysql_3')->executeSql("getRows",$sql3);
        return $data_3;
    }

    public function getUsers(){
        $data = $this->Db('mysql_0')->table('mm_user')->asTable('u')
            ->field('u.*,ui.birthday,ui.info,a.address_info,a.is_default')
            ->leftJoin('mm_user_info  ui on ui.user_id = u.id')
            ->leftJoin('mm_address  a on a.user_id =u.id')
            ->where()
            ->order('u.id desc')
            ->limit(0,15)
            ->select();
        return $data;
    }
    public function getUsers1(){
        $data = $this->Db('mysql_1')->table('q_user_01')->asTable('u')
            ->field('u.*')
            ->where()
            ->order('u.id desc')
            ->limit(0,3)
            ->select();
        return $data;
    }

    public function getCount(){
        //$this->getLastSql();
        $data_count = $this->Db('mysql_0')->table('mm_user')->asTable('u')
            ->field('u.*,ui.birthday,ui.info,a.address_info,a.is_default')
            ->leftJoin('mm_user_info  ui on ui.user_id = u.id')
            ->leftJoin('mm_address  a on a.user_id =u.id')
            ->where()
            ->count();
        return $data_count;
    }


}
