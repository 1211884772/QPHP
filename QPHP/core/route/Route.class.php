<?php

class Route
{

    protected $rules = array();
    protected $module='';//模块名称
    protected $action='';//控制器名称
    protected $mod='';//方法名称

    private static $ins=null;
    public static function instance(){
        if(!self::$ins||!(self::$ins instanceof self)){
            self::$ins = new self();
        }
        return self::$ins;
    }

    private function __construct()
    {
    }

    public function __destruct(){
        $this->module='';
        $this->action='';
        $this->mod='';
	}

    /**
     * 加载路由配置文件
     * @throws Exception
     */
    protected function requireRouteFileUrl(){
        $conf = array(
            'RouteUrl'=>APP_PATH .'route'//前端路由文件
        );
        Func::requireFileDir($conf);
    }

    protected  function get($rule, $route = '', array $option = [], array $pattern = []){
        //'index/age','index/IndexAction/age
        $this->addRoute($rule, $route,'GET',$option , $pattern );
    }

    protected function post($rule, $route, array $option = [], array $pattern = []){
        //'index/age','index/IndexAction/age
        $this->addRoute($rule, $route,'POST',$option , $pattern );
    }

    protected function put($rule, $route = '', array $option = [], array $pattern = []){
        //'index/age','index/IndexAction/age
        $this->addRoute($rule, $route,'PUT',$option , $pattern );
    }
    protected function patch($rule, $route = '', array $option = [], array $pattern = []){
        $this->addRoute($rule, $route,'PATCH',$option , $pattern );
    }

    protected function delete($rule, $route = '', array $option = [], array $pattern = []){
        $this->addRoute($rule, $route,'DELETE',$option , $pattern );
    }

    /**
     * 解析出模型，控制器，方法
     */
    protected function parsePath(){

        $url = '';
        if(isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
            if (strpos($url, '.php') != false) {
                $url = preg_replace("/\/\w*.php/", "", $url);
            }
            if(strpos($url,'?')!==false){
                $url = preg_replace("/\?[\w=&]*/", "", $url);
            }
            $url = preg_replace("/^\//", "", $url);
            $url = preg_replace("/\/$/", "", $url);
            //判断请求类型
            if(isset($_SERVER['REQUEST_METHOD'])&&$_SERVER['REQUEST_METHOD']){
                $method =strtolower($_SERVER['REQUEST_METHOD']);
                if(isset($this->rules[$method][$url])){
                    $url = $this->rules[$method][$url];
                }
            }
        }
        if(empty($url)){
            return ;
        }

        $_arr=explode('/',$url);

        if(isset($_arr[0])&&!empty($_arr[0])){
            $module = $_arr[0];
        }
        $this->module = isset($module)&&!empty($module)?strtolower($module):'';
        if(isset($_arr[1])&&!empty($_arr[1])){
            $action = $_arr[1];
        }
        $this->action = isset($action)&&!empty($action)?ucfirst($action).'Action':'';
        if(isset($_arr[2])&&!empty($_arr[2])){
            $mod = $_arr[2];
        }
        $this->mod = isset($mod)&&!empty($mod)?$mod:'';

    }

    public function __get($name){
        return $this->$name;
    }

    protected function rule($rule, $route, $method = '*', array $option = [], array $pattern = []){
        return $this->addRule($rule, $route, $method, $option, $pattern);
    }
    /**
     * 添加路由到数组
     * @param $rule
     * @param string $route
     * @param array $option
     * @param array $pattern
     */
    protected function addRoute($rule, $route = '', $method = '*',  $option = [],  $pattern = []){
        // 读取路由标识
        $method = strtolower($method);
        $this->rules[$method][$rule] = $route;
    }


    public function __call($method, $parameters)
    {
            return $this->$method(...$parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return (self::instance())->$method(...$parameters);
    }

}
