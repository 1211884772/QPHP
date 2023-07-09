<?php
namespace admin\Model;

use QPHP\core\model\Model;

/**
 * Class UserModel类
 */
class UserModel extends Model
{
    public $table='mm_user';//数据表
    public $key='id';//主键
    protected $dbType='mysql';//数据库类型
    public function __construct()
    {
        parent::__construct();
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
