<?php


class Func
{
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
    /**
     * 加载文件和目录下文件
     * @param array $conf
     * @throws Exception
     */
    protected function requireFileDir($conf=[]){
        foreach ($conf as $k=>$v){
            if(file_exists($v)){
                if(is_file($v)){
                    require_once $v;
                }else if (is_dir($v)){
                    $this->requireDir($v);
                }
                clearstatcache();
            }else{
                throw new Exception("The configuration file [{$v}] does not exist");
            }
        }
    }


    /**
     * 加载目录下所有文件
     * @param $dir
     */
    private function requireDir($dir)
    {
        $handle = opendir($dir);//打开文件夹
        while (false !== ($file = readdir($handle))) {//读取文件
            if ($file != '.' && $file != '..') {
                $filepath = $dir . '/' . $file;//文件路径

                if (filetype($filepath) == 'dir') {//如果是文件夹
                    $this->requireDir($filepath);//继续读
                } else {
                    if(is_file($filepath)){
                        require_once ($filepath);//引入文件
                    }
                    clearstatcache();
                }
            }
        }
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
