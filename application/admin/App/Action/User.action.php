<?php
namespace admin\Action;

use admin\Model\UserModel;
use admin\Util\lib\JsonUtil;
use admin\Validate\UserValidate;
use QPHP\core\cache\redis\R;
use QPHP\core\lang\Lang;

class UserAction extends CommonAction
{
    private ?UserModel $userModel=null;
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();

    }

    /**
     * 测试验证器
     */
    function testValidate(){
        var_dump(Lang::lang("name"));
        $lang=isset($this->input['lang'])?$this->input['lang']:'';
        $data = [
            'name' => '8gAg:',
            'username'=>'99654.78ww12et32.45fewabc',
            'test'=>'2321xxc'
        ];
        print("<pre>");
        print_r($data);
        $validate = new UserValidate();
         //$lang='cn';
        $validateResult = $validate->setLanguage($lang)->check($data)->onScene('insert')->Validate();
        if($validateResult !=true){
            $msg = $validate->getError();
            print("<pre>");
            print_r($msg);

            $msg2 = $validate->getAllErrors();
            print("<pre>");
            print_r($msg2);
        }
        $data1 = $validate->getData();
        print("<pre>");
        print_r($data1);

        //$validate = new UserValidate();
        //$lang='cn';
        $validateResult = $validate->setLanguage($lang)->check($data)->onScene('select')->Validate();

        if($validateResult !=true){
            $msg = $validate->getError();
            print("<pre>");
            print_r($msg);

            $msg2 = $validate->getAllErrors();
            print("<pre>");
            print_r($msg2);
        }
        $data1 = $validate->getData();
        print("<pre>");
        print_r($data1);

    }
    /**
     * redis
     */
    public function rCache(){
        R::getRedis()->set("name","mumu");
        echo R::getRedis()->get("name");
    }

    /**
     * memcache
     */
    public function mCache(){
        M::getMem()->set("name","mumu",60);
        echo M::getMem()->get("name");
        M::getMem()->remove('name');
    }

    public function index(){

        //获取登录用户id
        //$userData= $this->getUserId();
        //var_dump($userData);
       // echo $a;
        $status=opcache_get_status();
        echo var_export($status["opcache_enabled"],true).PHP_EOL;
        echo getenv("PHP_FCGI_MAX_REQUESTS").PHP_EOL;

        echo 'user hello';
        $model = $this->userModel;

        //$model->getLastSql();
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

        $dataS = $model->getUser00();
        echo '<pre>';
       // print_r($dataS);

        $data = $model->getUsers();
        echo '<pre>';
       // print_r($data);


       // $data1 = $model->getUsers1();
       // print_r($data1);



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

        $data_u['data_s']=$dataS;
        $data_u['data']=$data;
        $this->display('user/index.html',$data_u);
    }

    public function view(){

        $id = isset($this->input['id'])?$this->input['id']:0;
        echo $id;
        //$userModel = new UserModel();
        $data = $this->userModel->Db('mysql_0')->table('mm_user')->where("id={$id}")->find();
        $this->display('user/view.html',array(
            'data'=>$data
        ));
    }

    public function add(){

        extract($this->input);
//        $username = isset($username)?$username:'';
//        $pwd = isset($pwd)?$pwd:'';
//        $age = isset($age)?$age:0;
//        $address = isset($address)?$address:'';

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
            $param_data =array(
                'username'=>$username,
                'age'=>$age,
                'pwd'=>$pwd,
                'address'=>$address
            );
            $check_value = UserValidate::quick($param_data,'create');
            //$check_value = UserValidate::check($param_data);
            // 验证失败
            if ($check_value->isFail()) {
                $msg=$check_value->firstError();
                JsonUtil::echoJson(false, 0,$msg);
            }
            // 验证成功 ...
            $param_data =array(
                'username'=>$check_value->get('username'),
                'age'=>$check_value->get('age'),
                'pwd'=>$check_value->get('pwd'),
                'address'=>$check_value->get('address'),
            );

            $model = new UserModel();
            $last_id = $model->Db('mysql_0')->table('mm_user')->insert($param_data);

            if($last_id>0){
                $this->redireact('/admin/user/index/');
            }
        }

        $this->display('user/add.html');
    }


    public function edit(){
        extract($this->input);
        $id = isset($id)?$id:0;
//        $username = isset($username)?$username:'';
//        $pwd = isset($pwd)?$pwd:'';
//        $age = isset($age)?$age:0;
//        $address = isset($address)?$address:'';


        $model = new UserModel();
        if($isPost){
            $param_data =array(
                'username'=>$username,
                'age'=>$age,
                'pwd'=>$pwd,
                'address'=>$address
            );
            $check_value = UserValidate::check($param_data,[],[],'update');

            // 验证失败
            if ($check_value->isFail()) {
                //var_dump($check_value->getErrors());
                $msg=$check_value->firstError();
                JsonUtil::echoJson(false, 0,$msg);
            }
            // 验证成功 ...
            $safeData = $check_value->get('username'); // 验证通过的安全数据
            //$safeData = $check_value->getSafeData(); // 验证通过的安全数据
            // $postData = $check_value->all(); // 原始数据
            var_dump($safeData);
            $param_data =array(
                'username'=>$check_value->get('username'),
                'age'=>$check_value->get('age'),
                'pwd'=>$check_value->get('pwd'),
                'address'=>$check_value->get('address'),
            );

            $where ="id={$id}";
            $res=$model->Db('mysql_0')->table('mm_user')->where($where)->update($param_data);
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
        $param_data['id']=$id;
        if($id>0){
            $check_value = UserValidate::quick($param_data,'delete');
            // 验证失败
            if ($check_value->isFail()) {
                $msg=$check_value->firstError();
                JsonUtil::echoJson(false, 0,$msg);
            }
            // 验证成功 ...
            $safeData = $check_value->getSafeData(); // 验证通过的安全数据
            var_dump($safeData);
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
