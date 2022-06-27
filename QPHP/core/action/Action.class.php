<?php


class Action
{

    public $input;

    public function __construct()
    {
        //TODO: 此处不符合，迪米特法则
        //陌生的类QDbPdoPool最好不要以局部变量的形式出现在类的内部
        $input = new Input();
        $this->input = $input->parse();
    }

    //前置执行
    public function before(){

    }

    //后置执行
    public function after(){

    }

    public function call($actionObj,$action){
        $this->before();
        $actionObj->$action();
        $this->after();
    }

    public function display($view,$data=array()){
		global $MODULE;
        //extract() 函数从数组中将变量导入到当前的符号表
        extract($data);
        require APP_PATH.'application/'.$MODULE.'/App/View/'.$view;
    }

    public function redireact($url){
        echo "<script>location.href='{$url}'</script>";
    }

}
