<?php


class UserAction extends CommonAction
{

//    public function getUser01(){
//        $model = new UserModel();
//        return $this->getUser01();
//    }
//    public function getUser02(){
//        $model = new UserModel();
//        return $this->getUser02();
//    }
//    public function getUser03(){
//        $model = new UserModel();
//        return $this->getUser03();
//    }

    public function index(){

        //获取登录用户id
        //$userData= $this->getUserId();
        //var_dump($userData);


        echo 'user hello';
        $model = new UserModel();
 //       $th1=new Thread();//10个线程
        //$th2=new Thread();//10个线程
        //$th3=new Thread();//10个线程
       // $a=new UserAction();
  //      $data_1 = $th1->exec($model,"getUser01");//执行行自定义的函数
//        $data_2 = $th2->exec($a,"getUser02");//执行行自定义的函数
//        $data_3 = $th3->exec($a,"getUser03");//执行行自定义的函数

//        var_dump($data_1);
//        var_dump($data_2);
//        var_dump($data_3);

//        $data = array_merge($data_2,$data_3);
//        $sort_c = array_column($data,'create_time');
//        $sort_u = array_column($data,'updat_time');
//        array_multisort($sort_c, SORT_DESC,SORT_NUMERIC,$sort_u,SORT_DESC,SORT_NUMERIC,$data);
//
//        echo '<pre>';
//        print_r($data);

        $datas=[];
        $data=[];
        $datas = $model->getUser();
        echo '<pre>';
        print_r($datas);

        $data = $model->getUsers();
        echo '<pre>';
        print_r($data);
        $data_count = $model->getCount();
        echo '<pre>';
        print_r($data_count);

        echo "==========================";

        //$model = new UserModel();
   /*     $arr2 = $model->Db('mysql_1')->table('mm_user')->asTable('u')
            ->field('u.*,ui.birthday,ui.info,a.address_info,a.is_default')
            ->leftJoin('mm_user_info  ui on ui.user_id = u.id')
            ->leftJoin('mm_address  a on a.user_id =u.id')
            ->where('u.id = 1')
            ->find();
        echo '<pre>';
        print_r($arr2);*/

        $data_u['data_s']=$datas;
        $data_u['data']=$data;
        $this->display('user/index.html',$data_u);
    }

    public function view(){
        extract($this->input);
        $id = isset($id)?$id:0;
        $model = new UserModel();
        $data = $model->Db('mysql_0')->table('mm_user')->where("id={$id}")->find();
        $this->display('user/view.html',array(
            'data'=>$data
        ));
    }

    public function add(){

        extract($this->input);
        $username = isset($username)?$username:'';
        $password = isset($password)?$password:'';
        $age = isset($age)?$age:0;
        $address = isset($address)?$address:'';

        /*
        //=================此处验证未测试===============
        // 验证 POST 数据
        $v = UserValidate::check($_POST);

// 验证失败
        if ($v->isFail()) {
            var_dump($v->getErrors());
            var_dump($v->firstError());
        }
// 验证成功 ...
        $safeData = $v->getSafeData(); // 验证通过的安全数据
// $postData = $v->all(); // 原始数据
*/

        if($isPost){
            $model = new UserModel();
            $data =array(
                'username'=>$username,
                'age'=>$age,
                'pwd'=>$password,
                'address'=>$address
            );
            $last_id = $model->Db('mysql_0')->table('mm_user')->insert($data);

            if($last_id>0){
                $this->redireact('/admin/user/index/');
            }
        }

        $this->display('user/add.html');
    }


    public function edit(){
        extract($this->input);
        $id = isset($id)?$id:0;
        $username = isset($username)?$username:'';
        $pwd = isset($pwd)?$pwd:'';
        $age = isset($age)?$age:0;
        $address = isset($address)?$address:'';
        $model = new UserModel();
        if($isPost){
            $arr =array(
                'username'=>$username,
                'age'=>$age,
                'pwd'=>$pwd,
                'address'=>$address
            );
            $where ="id={$id}";
            $res=$model->Db('mysql_0')->table('mm_user')->where($where)->update($arr);
            if($res){
                $this->redireact('/admin/user/index/');
            }
        }
        $data = $model->Db('mysql_0')->table('mm_user')->where("id ={$id}")->find();
        $this->display('user/edit.html',array(
            'data'=>$data
        ));
    }


    public function del(){
        extract($this->input);
        $id = isset($id)?$id:0;
        if($id>0){
            $model = new UserModel();
            $where ="id={$id}";
            $res= $model->Db('mysql_0')->table('mm_user')->where($where)->delete();
            if($res){
                $this->redireact('/admin/user/index/');
            }
        }
    }


    public function test(){
        echo 'test user';
        $test = '这是一个测试版本';
        $data =array(
            'test'=>$test
        );
        $this->display('user/test.html',$data);
    }
}
