<?php
/**
 * 中间件 入口文件
 */
 
// 定义 中间件路径
define('MODULE_PATH', dirname(__FILE__).'/modules/');

// 加载框架
require('ChelerApi/ChelerApi.php');

// 加载自定义配置文件
require('conf/conf.php');

// 执行
\ChelerApi\ChelerApi::run();