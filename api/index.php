<?php
/**
 * 程序入口文件
 */
 // phpinfo(INFO_MODULES);
// 定义应用程序路径
define('APP_PATH', dirname(__FILE__).'/app/');

// 加载框架
require('ChelerApi/ChelerApi.php');

// 加载自定义配置文件
require('conf/conf.php');

// 执行
\ChelerApi\ChelerApi::run();