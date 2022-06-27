<?php


class IndexAction extends CommonAction
{
    public function index(){


        //http://www.qphp.com/index/index/?user_id=1
        echo $this->input['id'];
        echo 'hello index';
        $name = 'yzm';
        $age = 5;
        $data = array(
            'arr'=>array('name'=>'QPHP','age'=>1),
            'name'=>$name,
            'age'=>$age
        );
        if(true){
            //throw new Exception('错误了',-1);
        }

        $this->display('index/index.html',$data);
    }

    //memcache
    public function mem(){

        dump('eee');
       // setVar('name','yzm');
        //echo getVar('name');
        delVal('name');
    }

    //redis
    public function redis_test(){
        $redis = R();
        //$redis->set('name','good');
        $name = $redis->get('name');
        var_dump($name);
        //$redis->lpush('sql','select * from user');
        $sql =$redis->rpop('sql');

        var_dump($sql);
    }


    public function age(){
        echo $this->input['id'];
        echo 'admin/ages';
    }

    public function name(){
        echo 'admin/name';
    }
}
