<?php


namespace QPHP\core\cache\memcache;


use QPHP\core\cache\redis\QRedis;
use QPHP\core\container\Container;

class M
{
    private static $ins=null;

    /**
     * 单例
     * @return R|null
     */
    public static function instance(){
        if(is_null(self::$ins)||!(self::$ins instanceof R)){
            self::$ins = new self();
        }
        return self::$ins;
    }

    /**
     * 实例化Qredis
     * @return QRedis
     */
    private function getMem(){
        // 绑定服务，使用闭包工厂注入自定义参数
        //$mem = new MmCache(MEM_POOL["mem_0"]["MEM_HOST"],MEM_POOL["mem_0"]["MEM_PORT"]);
        return Container::getInstance()->makeWith(MmCache::class, [MEM_POOL["mem_0"]["MEM_HOST"],MEM_POOL["mem_0"]["MEM_PORT"]]);
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
