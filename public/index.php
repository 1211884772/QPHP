<?php
namespace index;


use QPHP\Base;

// [ 应用入口文件 ]
// 加载基础文件
require __DIR__ . '/../QPHP/base.php';
// 支持事先使用静态方法设置Request对象和Config对象

// 执行应用并响应
Base::run();
