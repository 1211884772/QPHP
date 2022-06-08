<?php


class UserAction extends CommonAction
{
    public function index(){

        //获取登录用户id
        //$userData= $this->getUserId();
        //var_dump($userData);

        echo 'user hello';
        $model = new UserModel();
        //$data = $model->model->findAll();
        $data = $model->model->table('mm_user')->asTable('u')
            ->field('u.*,ui.birthday,ui.info,a.address_info,a.is_default')
            ->leftJoin('mm_user_info  ui on ui.user_id = u.id')
            ->leftJoin('mm_address  a on a.user_id =u.id')
            ->where()
            ->order('u.id desc')
            ->limit(0,10)
            ->select();
        echo '<pre>';
        print_r($data);
        $data_count = $model->model->table('mm_user')->asTable('u')
            ->field('u.*,ui.birthday,ui.info,a.address_info,a.is_default')
            ->leftJoin('mm_user_info  ui on ui.user_id = u.id')
            ->leftJoin('mm_address  a on a.user_id =u.id')
            ->where()
            ->count();
        echo $model->model->getLastSql();
        echo '<pre>';
        print_r($data_count);


        echo "==========================";
       // $arr2 = $model->model->table('mm_user')->key('id')->find(1);
        $model = new UserModel();
        $arr2 = $model->model->table('mm_user')->asTable('u')
            ->field('u.*,ui.birthday,ui.info,a.address_info,a.is_default')
            ->leftJoin('mm_user_info  ui on ui.user_id = u.id')
            ->leftJoin('mm_address  a on a.user_id =u.id')
            ->where('u.id = 1')
            ->find();
        echo '<pre>';
        print_r($arr2);

        $this->display('user/index.html',$data);
    }

    public function view(){
        extract($this->input);
        $id = isset($id)?$id:0;
        $model = new UserModel();
        $data = $model->model->find($id);
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
            $last_id = $model->model->table('mm_user')->insert($data);

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
            $res=$model->model->table('mm_user')->where($where)->update($arr);
            if($res){
                $this->redireact('/admin/user/index/');
            }
        }
        $data = $model->model->table('mm_user')->where("id ={$id}")->find();
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
            $res= $model->model->table('mm_user')->where($where)->delete();
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
