<?php
namespace ChelerApi;
use ChelerApi\runtime\CException;
use ChelerApi\runtime\CClassLoader;
define('CHELER_API_ROOT', __DIR__.'/');

require(CHELER_API_ROOT. 'conf.php'); //导入框架配置类
require(CHELER_API_ROOT. 'runtime/NativePatch.php');
require(CHELER_API_ROOT. 'runtime/CCore.php');

/**
 * 框架导出类
 */
class ChelerApi extends \ChelerApi\runtime\CCore{

	public static $time;
	
	/**
	 * 安装类加载器
	 */
	private static function setupLoader() {
	    require(CHELER_API_ROOT. 'runtime/CClassLoader.php');
	    CClassLoader::registerNamespace('ChelerApi', CHELER_API_ROOT);
	    CClassLoader::registerNamespace('modules', MODULE_PATH);
	    CClassLoader::register();
	}
	
	/**
	 * 框架启动函数
	 */
	public static function run() {
	    self::setupLoader();
	    
		try {
		    // 路由分发, 执行
			$router = ChelerApi::loadclass('ChelerApi\runtime\CRouter');
			$router->router();
			$router->exec();
		}
		catch (\Exception $e) {
			CException::errorTpl($e);
		}
	}

	/**
	 * 框架实例化php类函数，单例模式
	 * 全局使用方法：ChelerApi::loadclass($classname)
	 * @param string $classname
	 * @return object
	 */
	public static function loadclass($classname) {
	    return parent::load($classname);
	}

	/**
	 * 获取时间戳
	 * 1. 静态时间戳函数
	 * 全局使用方法：ChelerApi::getTime();
	 * @param $msg
	 * @return html
	 */
	public static function getTime() {
		if (self::$time > 0) return self::$time;
		self::$time = time();
		return self::$time;
	}

	/**
	 * 获取全局配置文件
	 * 全局使用方法：ChelerApi::getConfig('controller.path')
	 * @param string $path 获取的配置路径 多级用点号分隔
	 * @return mixed
	 */
	public static function getConfig($path='') {
		global $_CONFIG_;
		if (empty($path)) return $_CONFIG_;
		$tmp = $_CONFIG_;
		$paths = explode('.', $path);
		foreach ($paths as $item) {
			$tmp = $tmp[$item];
		}
		return $tmp;
	}

	/**
	 * 设置配置文件，框架意外慎用！
	 * @param $key
	 * @param $value
	 */
	public static function setConfig($key, $value) {
		global $_CONFIG_;
		$_CONFIG_[$key] = $value;
		return $_CONFIG_;
	}

	/**
	 * 获取项目路径
	 * 全局使用方法：ChelerApi::getAppPath('controller.path')
	 * @param $path
	 * @return String
	 */
	public static function getAppPath($path = '') {
	    $path = rtrim($path, '/');
		if (!defined('APP_PATH')) return $path;
		return rtrim(APP_PATH, '/') . '/' . $path;
	}

	/**
	 * 【静态】基础服务层
	 * 全局使用方法：ChelerApi::getRPCService($servicename, $version)
	 * @param string $servicename 完整服务名 包名.服务
	 * @param integer $version 版本, 默认为1
	 * @return ChelerApi\runtime\CBaseService
	 */
	public static function getBaseService($serviceName) {
	    static $objs = [];
	    $class_name = '\\modules\\BaseService\\'.$serviceName .'Service';
	    $hash = md5($class_name);
	    if (!isset($objs[$hash])) {
	        $objs[$hash] = new $class_name;
	    }
	     
	    return $objs[$hash];
	}
	
	/**
	 * 【静态】获取逻辑服务层
	 * @param string $serviceName 服务层名称
	 * @return ChelerApi\runtime\CLogicService
	 */
	public static function getLogicService($serviceName) {
	    static $objs = [];
	    $hash = md5($serviceName);
	    if (!isset($objs[$hash])) {
	        $class_name = '\\modules\\LogicService\\'.$serviceName .'Service';
	        $objs[$hash] = new $class_name;
	    }
	     
	    return $objs[$hash];
	}
	
	/**
	 * 【静态】获取数据层
	 * @param string $daoName 数据层名称
	 * @return ChelerApi\dao\CDao
	 */
	public static function getDao($daoName) {
	    static $objs = [];
	    $class_name = '\\modules\\dao\\'.$daoName .'Dao';
	    $hash = md5($class_name);
	    if (!isset($objs[$hash])) {
	        $objs[$hash] = new $class_name;
	    }
	
	    return $objs[$hash];
	}
	
	/**
	 * 框架错误机制
	 * @param $msg
	 * @return html
	 */
	public static function throwError($msg, $code = 10000) {
		throw new \ChelerApi\runtime\CException($msg, $code);
	}

	/**
	 * 返回404错误页面
	 */
	public static function return404() {
		header('HTTP/1.1 404 Not Found');
		header("status: 404 Not Found");
		self::_error_page("404 Not Found");
		exit;
	}

	/**
	 * 返回405错误页面
	 */
	public static function return405() {
		header('HTTP/1.1 405 Method not allowed');
		header("status: 405 Method not allowed");
		self::_error_page("405 Method not allowed");
		exit;
	}

	/**
	 * 返回500错误页面
	 */
	public static function return500() {
		header('HTTP/1.1 500 Internal Server Error');
		header("status: 500 Internal Server Error");
		self::_error_page("500 Internal Server Error");
		exit;
	}
	
	private static function _error_page($msg) {}
}

/**
 * 控制器Controller基类
 */
class Dao extends \ChelerApi\runtime\CCore {

    /**
     * @var \ChelerApi\dao\CDao
     */
    protected $dao;
    protected $table_name;

    /**
     * 初始化
     */
    public function __construct() {
        parent::__construct();
        
        $this->dao = $this->load('\ChelerApi\dao\CDao'); //导入Dao
        $this->dao->run_db();
    }
    
    /**
     * 分库初始化DB
     * 如果有多数据库链接的情况下，会调用该函数来自动切换DB
     * @param string $db
     * @return \ChelerApi\dao\db\DB
     */
    public function init_db($db = 'default') {
        $this->dao->db->db($db);
        return $this->dao->db;
    }
    /** 
     * 存储缓存
     * @param $key 主键
     * @param $data 缓存数据
     * @author hanliqiang
     */
    protected function set_cache($key, array $data, $time = 0){
        $key = $this->_cache_key($key);
        self::getCache()->set($key, $data);
    }
    /**
     * 获取缓存
     * @param $key 主键
     * @param $data 缓存数据
     * @author hanliqiang
     */
    protected function get_cache($key){
        $key = $this->_cache_key($key);
        self::getCache()->get($key);
    }
    /**
     * 获取缓存
     * @param $key 主键
     * @param $data 缓存数据
     * @author hanliqiang
     */
    protected function clear_cache($key){
        $key = $this->_cache_key($key);
        self::getCache()->clear($key);
    }
    /**
     * 缓存KEY
     * @param string $key
     */
    private function _cache_key($key) {
        return $this->table_name . '_' . $key;
    }
    
    /**
     * 开始事务操作
     * DAO中使用方法：$this->dao->db->transaction_start()
     * @author lxm update 2013-12-13
     */
    public function transaction_start() {
    	$this->init_db($this->db)->transaction_start();
    }
    /**
     * 提交事务
     * DAO中使用方法：$this->dao->db->transaction_commit()
     * @author lxm update 2013-12-13
     */
    public function transaction_commit() {
    	$this->init_db($this->db)->transaction_commit();
    }
    
    /**
     * 回滚事务
     * DAO中使用方法：$this->dao->db->transaction_rollback()
     * @author lxm update 2013-12-13
     */
    public function transaction_rollback() {
    	$this->init_db($this->db)->transaction_rollback();
    }
}