<?php


class Config
{
    private $configs = [];
    private $configModules=[];
    private $configFileUrls = [];
    private static $ins=null;
    public static function instance(){
        if(!self::$ins||!(self::$ins instanceof self)){
            self::$ins = new self();
        }
        return self::$ins;
    }
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->configModules=[];
        $this->configFileUrls = [];
    }

    private function setConfigs($APP_PATH){
        $this->configs=array(
            'ConfigUrl'=>$APP_PATH .'config'//全局配置文件
        );
    }

    private function setConfigModules($APP_PATH,$MODULE){
        $this->configModules=array(
            'ConfigModuleUrl'=>$APP_PATH.'application/'.$MODULE.'/Config'//项目配置配置文件
        );
    }


    /**
     * 加载全局配置和项目配置文件文件
     */
    public function requireConfigFileUrl($APP_PATH){
        $this->configFileUrls=[];
        $this->setConfigs($APP_PATH);
        $this->requireFileDir($this->configs);

        $config_key = 'QPHP_CONFIG';
        $config[$config_key] = [];
        foreach ($this->configFileUrls as $val){
            require_once ($val);//引入文件
            if(isset($config['app'])){
                //extract($config['app']);
                $config[$config_key] = $this->arrayMerge($config[$config_key],$config['app']);
				unset($config['app']);
            }
        }
        define($config_key,$config[$config_key]);
    }

    /**
     * 项目配置文件文件
     */
    public function requireConfigModuleFileUrl($APP_PATH,$MODULE){
        $this->configFileUrls=[];
        $this->setConfigModules($APP_PATH,$MODULE);
        $this->requireFileDir($this->configModules);
        $config_key = strtoupper('QPHP_CONFIG_'.$MODULE);
        $config[$config_key] = [];
        foreach ($this->configFileUrls as $val){
            require_once ($val);//引入文件
            if(isset($config['app'])){
                //extract($config['app']);
                $config[$config_key]= $this->arrayMerge($config[$config_key],$config['app']);
				unset($config['app']);
            }
        }
        $config_arr = QPHP_CONFIG;
        $config_arr = $this->arrayMerge($config_arr,$config[$config_key]);
        define($config_key,$config_arr);
    }
    /**
     * 加载文件和目录下文件
     * @param array $conf
     * @throws Exception
     */
    private function requireFileDir($conf=[]){
        foreach ($conf as $k=>$v){
            if(file_exists($v)){
                if(is_file($v)){
                    $this->configFileUrls []= $v;
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
                        $this->configFileUrls []= $filepath;
                    }
                    clearstatcache();
                }
            }
        }
    }

    /**
     * 合并多维数组
     * @param $arr1
     * @param $arr2
     * @return array
     */
    private function arrayMerge(&$arr1, &$arr2){
        if(empty($arr1)){
            $arr1 = array_merge_recursive([], $arr2);
            return $arr1;
        }
        foreach($arr1 as $k1 => &$v1){
            foreach($arr2 as $k2 => &$v2){
                if( $k1 === $k2 ){
                    if( is_array($v1) && is_array($v2) ){
                        $v1 = $this->arrayMerge($v1, $v2);
                    }else{
                        $v1 = $v2;
                    }
                }
            }
        }
        return $arr1 = array_merge($arr1, array_diff_key($arr2, $arr1));
    }

}
