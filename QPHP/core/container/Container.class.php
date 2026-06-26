<?php

namespace QPHP\core\container;

use QPHP\core\model\mysql\MysqlM;
use QPHP\core\model\oracle\OracleM;
use RuntimeException;
use ReflectionClass;
use ReflectionException;

/**
 * 工业级 PHP 服务容器 / DI 容器
 * 特性：
 * 1. 普通绑定 / 单例绑定
 * 2. 闭包工厂解析
 * 3. 递归自动依赖注入（构造器注入）
 * 4. 服务别名
 * 5. 实例缓存、延迟解析
 * 6. 完善异常抛出，生产环境健壮
 */
class Container
{
    /**
     * 全局容器实例（静态入口，全局唯一容器）
     * @var self|null
     */
    protected static ?self $instance = null;

    /**
     * 服务绑定表：abstract => concrete
     * @var array<string, mixed>
     */
    protected array $bindings = [];

    /**
     * 标记是否为单例服务
     * @var array<string, bool>
     */
    protected array $singletons = [];

    /**
     * 已解析完成的实例缓存（单例复用）
     * @var array<string, object>
     */
    protected array $instances = [];

    /**
     * 服务别名
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * 获取全局容器单例
     * @return self
     */
    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    /**
     * 私有构造，禁止外部 new，统一使用单例
     */
    private function __construct()
    {
    }

    /**
     * 绑定服务（普通多例）
     * @param string $abstract 抽象/接口/类名
     * @param mixed $concrete 具体类名 / 闭包工厂
     * @return void
     */
    public function bind(string $abstract, $concrete): void
    {
        $abstract = $this->getAlias($abstract);
        $this->bindings[$abstract] = $concrete;
        unset($this->singletons[$abstract], $this->instances[$abstract]);
    }

    /**
     * 绑定单例服务（全局只创建一次）
     * @param string $abstract
     * @param mixed $concrete
     * @return void
     */
    public function singleton(string $abstract, $concrete): void
    {
        $abstract = $this->getAlias($abstract);
        $this->bindings[$abstract] = $concrete;
        $this->singletons[$abstract] = true;
        unset($this->instances[$abstract]);
    }

    /**
     * 设置服务别名
     * @param string $alias 别名
     * @param string $abstract 原标识
     * @return void
     */
    public function alias(string $alias, string $abstract): void
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * 解析别名，拿到真实服务名
     * @param string $name
     * @return string
     */
    protected function getAlias(string $name): string
    {
        while (isset($this->aliases[$name])) {
            $name = $this->aliases[$name];
        }
        return $name;
    }

    /**
     * 判断服务是否已绑定
     * @param string $abstract
     * @return bool
     */
    public function has(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);
        return isset($this->bindings[$abstract]);
    }

    /**
     * 判断是否为单例
     * @param string $abstract
     * @return bool
     */
    public function isSingleton(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);
        return isset($this->singletons[$abstract]);
    }

    /**
     * 获取/解析服务实例（入口方法）
     * @param string $abstract
     * @return object
     * @throws RuntimeException
     */
    public function make(string $abstract): object
    {
        $abstract = $this->getAlias($abstract);

        // 单例：已解析直接返回缓存实例
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->getConcrete($abstract);
        $instance = $this->resolve($concrete);

        // 单例写入缓存
        if ($this->isSingleton($abstract)) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }
    /**
     * 调用静态工厂方法 newClass 创建实例
     * @param string $className
     * @param array $args
     * @return object
     * @throws RuntimeException
     */
    public function makeByFactory(string $className,string $method="newClass", array $args = []): MysqlM|OracleM
    {
        $className = $this->getAlias($className);

        if (!class_exists($className)) {
            throw new RuntimeException("类不存在：{$className}");
        }

        if (!method_exists($className, 'newClass')) {
            throw new RuntimeException("类 {$className} 不存在静态方法 newClass");
        }

        return $className::$method(...$args);
    }
    /**
     * 携带自定义构造参数实例化，不写入单例缓存
     * @param string $abstract
     * @param array $parameters 构造参数数组
     * @return object
     */
    public function makeWith(string $abstract, array $parameters = []): object
    {
        $abstract = $this->getAlias($abstract);
        $concrete = $this->getConcrete($abstract);

        return $this->resolveWithParameters($concrete, $parameters);
    }

    /**
     * 支持外部参数覆盖构造函数入参
     * @param mixed $concrete
     * @param array $params
     * @return object
     * @throws RuntimeException
     */
    protected function resolveWithParameters($concrete, array $params = []): object
    {
        if (is_callable($concrete)) {
            return $concrete($this, $params);
        }

        if (!is_string($concrete) || !class_exists($concrete)) {
            throw new RuntimeException("服务解析失败：{$concrete} 不是有效类");
        }

        try {
            $ref = new ReflectionClass($concrete);
            $constructor = $ref->getConstructor();

            if (null === $constructor) {
                return new $concrete();
            }

            $arguments = [];
            $index = 0;
            foreach ($constructor->getParameters() as $param) {
                // 优先使用外部传入参数
                if (isset($params[$index])) {
                    $arguments[] = $params[$index];
                    $index++;
                    continue;
                }

                // 无传入参数，走自动DI逻辑
                $class = $param->getType();
                if ($class) {
                    $arguments[] = $this->make($class->getName());
                } elseif ($param->isDefaultValueAvailable()) {
                    $arguments[] = $param->getDefaultValue();
                } else {
                    throw new RuntimeException(
                        "类 {$concrete} 参数 {$param->name} 缺少传入值且无默认值"
                    );
                }
            }

            return $ref->newInstanceArgs($arguments);
        } catch (ReflectionException $e) {
            throw new RuntimeException("反射实例异常：" . $e->getMessage());
        }
    }

    /**
     * 获取绑定的具体实现
     * @param string $abstract
     * @return mixed
     */
    protected function getConcrete(string $abstract)
    {
        // 未手动绑定，则认为抽象=具体类名
        if (!isset($this->bindings[$abstract])) {
            return $abstract;
        }
        return $this->bindings[$abstract];
    }

    /**
     * 核心解析：递归创建实例 + 自动构造依赖注入
     * @param mixed $concrete
     * @return object
     * @throws RuntimeException
     */
    protected function resolve($concrete): object
    {
        // 1. 如果是闭包工厂，直接执行闭包返回实例
        if (is_callable($concrete)) {
            return $concrete($this);
        }

        // 2. 字符串类名，反射解析构造依赖
        if (!is_string($concrete) || !class_exists($concrete)) {
            throw new RuntimeException("服务解析失败：{$concrete} 不是有效类或可调用工厂");
        }

        try {
            $reflection = new ReflectionClass($concrete);

            // 无构造方法，直接 new
            $constructor = $reflection->getConstructor();
            if ($constructor === null) {
                return new $concrete();
            }

            // 解析构造参数，递归注入依赖
            $dependencies = [];
            foreach ($constructor->getParameters() as $param) {
                $paramClass = $param->getType();

                if ($paramClass === null) {
                    // 非类类型参数，必须有默认值，否则无法自动注入
                    if (!$param->isDefaultValueAvailable()) {
                        throw new RuntimeException(
                            "类 {$concrete} 构造参数 {$param->getName()} 无类型、无默认值，无法自动注入"
                        );
                    }
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    // 依赖其他类，递归 make
                    $depClass = $paramClass->getName();
                    $dependencies[] = $this->make($depClass);
                }
            }

            // 传入依赖，实例化
            return $reflection->newInstanceArgs($dependencies);

        } catch (ReflectionException $e) {
            throw new RuntimeException("反射解析类 {$concrete} 失败：" . $e->getMessage());
        }
    }

    /**
     * 清空所有绑定、实例、别名（框架重启/测试用）
     * @return void
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->singletons = [];
        $this->instances = [];
        $this->aliases = [];
    }
}