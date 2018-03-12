<?php
/* 框架全局配置常量 */ 
error_reporting(E_ALL);

/* 框架全局配置变量 */
/*********************************基础配置*****************************************/
$_CONFIG_ = [];

// 站点URL配置
$_CONFIG_['url'] = 'http://capi.com/';

// 是否开启调试
$_CONFIG_['debug'] = true;

// rpc配置
$_CONFIG_['rpc']['address'] = 'http://127.0.0.1:8081/';
$_CONFIG_['rpc']['timeout'] = 30;

/**
 * 路由访问方式
 * 1. default：index.php?m=user&c=index&a=run
 * 2. rewrite：/user/index/run?id=100
 */
$_CONFIG_['uriType'] = 'default';

/*********************************本地Service配置*****************************************/
$_CONFIG_['localService']['path'] = 'library/localService'; // 包含localService的路径

/*********************************Controller配置*****************************************/
/**
 * Controller控制器配置参数
 * 1. 你可以配置控制器默认的文件夹，默认的后缀，Action默认后缀，默认执行的Action和Controller
 * 2. 一般情况下，你可以不需要修改该配置参数
 * 3. $_CONFIG_['ismodule']参数，当你的项目比较大的时候，可以选用module方式，
 * 开启module后，你的URL种需要带m的参数，原始：index.php?c=index&a=run, 加module：
 * index.php?m=user&c=index&a=run , module就是$_CONFIG_['controller']['path']目录下的
 * 一个文件夹名称，请用小写文件夹名称
 */

$_CONFIG_['controller']['path']                  = 'controller/';
$_CONFIG_['controller']['controller_postfix']    = 'Controller'; //控制器文件后缀名
$_CONFIG_['controller']['action_postfix']        = ''; //Action函数名称后缀
$_CONFIG_['controller']['default_controller']    = 'index'; //默认执行的控制器名称
$_CONFIG_['controller']['default_action']        = 'run'; //默认执行的Action函数
$_CONFIG_['controller']['default_before_action'] = 'before'; //默认前置的ACTION名称
$_CONFIG_['controller']['default_after_action']  = 'after'; //默认后置ACTION名称


/*********************************Memcache缓存配置*****************************************/
/**
 * 缓存配置参数
 * 1. 您如果使用缓存 需要配置memcache的服务器和文件缓存的缓存路径
 * 2. memcache可以配置分布式服务器，根据$_CONFIG_['memcache'][0]的KEY值去进行添加
 * 3. 根据您的实际情况配置
 */
$_CONFIG_['memcache'][0]   = ['127.0.0.1', '11211'];


/*********************************Redis配置*****************************************/
$_CONFIG_['redis']['host'] = '127.0.0.1';
$_CONFIG_['redis']['port'] = 6379;           // int
$_CONFIG_['redis']['pass'] = '';
$_CONFIG_['redis']['db'] = 0;               // int

/********************************* 全局版本配置 ********************************************/

// 每个版本单独来个vxxx
// $_CONFIG_['apiVersion']['v100'] = [
//     // 控制器名 => 方法名列表
//     'index' => ['run']
// ];

