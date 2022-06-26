<?php

/**
 * Class UserModel类
 */
class UserModel extends Model
{
    public $table='mm_user';//数据表
    public $key='id';//主键
    protected $dbType='oracle';


    public function getUser(){
        $data = $this->Db("oracle_0")->table('"mm_user"')->asTable('u')
            ->field('u.*,ui."birthday",ui."info",a."address_info",a."is_default"')
            ->leftJoin('"mm_user_info"  ui on ui."user_id" = u."id"')
            ->leftJoin('"mm_address"  a on a."user_id" =u."id"')
            ->where()
            ->order('u."id" desc')
            ->limit(0,2)
            ->select();
        return $data;
    }


    public function getCount(){
        $data_count = $this->Db("oracle_0")->table('"mm_user"')->asTable('u')
            ->field('u.*,ui."birthday",ui."info",a."address_info",a."is_default"')
            ->leftJoin('"mm_user_info"  ui on ui."user_id" = u."id"')
            ->leftJoin('"mm_address"  a on a."user_id" =u."id"')
            ->where()
            ->count();
        return $data_count;
    }
}
