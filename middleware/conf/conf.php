<?php
/* 框架全局配置常量 */ 
error_reporting(E_ERROR);

/* 框架全局配置变量 */
/*********************************基础配置*****************************************/
$_CONFIG_ = [];

// 站点URL配置
$_CONFIG_['url'] = 'http://capi.com/';

// 是否开启调试
$_CONFIG_['debug'] = true;

/**
 * Memcache配置
 */
$_CONFIG_['memcache'][0]   = ['127.0.0.1', '11211'];

/**
 * MongoDB配置
 */
$_CONFIG_['mongo']['default']['server']     = '127.0.0.1';
$_CONFIG_['mongo']['default']['port']       = '27017';
$_CONFIG_['mongo']['default']['option']     = ['connect' => true];
$_CONFIG_['mongo']['default']['db_name']    = 'test';
$_CONFIG_['mongo']['default']['username']   = '';
$_CONFIG_['mongo']['default']['password']   = '';

/**
 * Redis配置，如果您使用了redis，则需要配置
 */
$_CONFIG_['redis']['default']['server']     = '127.0.0.1';
$_CONFIG_['redis']['default']['port']       = '6379';

$_CONFIG_['db']['default']['db_type']                   = 0; //0-单个服务器，1-读写分离，2-随机
$_CONFIG_['db']['default'][0]['host']                   = '127.0.0.1'; //主机
$_CONFIG_['db']['default'][0]['username']               = 'root'; //数据库用户名
$_CONFIG_['db']['default'][0]['password']               = ''; //数据库密码
$_CONFIG_['db']['default'][0]['database']               = 'test'; //数据库
$_CONFIG_['db']['default'][0]['charset']                = 'utf8'; //数据库编码

$_CONFIG_['db']['test1']['db_type']                      = 0;
$_CONFIG_['db']['test1'][0]['host']                      = '127.0.0.1'; //主机
$_CONFIG_['db']['test1'][0]['username']                  = 'root'; //数据库用户名
$_CONFIG_['db']['test1'][0]['password']                  = ''; //数据库密码
$_CONFIG_['db']['test1'][0]['database']                  = 'test1'; //数据库
$_CONFIG_['db']['test1'][0]['charset']                   = 'utf8'; //数据库编码
