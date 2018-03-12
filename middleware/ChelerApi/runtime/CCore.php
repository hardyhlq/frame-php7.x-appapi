<?php
namespace ChelerApi\runtime;
use ChelerApi\ChelerApi;
/**
 * 核心类
 * 
 * 保存单例加载的实例
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class CCore {
    
    public static $instance = []; //单例容器
	
	/**
	 * 初始化
	 */
	public function __construct() {
		$this->run_register_global(); //注册全局变量
	}
	
	/**
	 * 框架核心加载-框架的所有类都需要通过该函数出去
	 * 1. 单例模式
	 * 2. 可以加载-Controller，Service，View，Dao，Util，Library中的类文件
	 * 3. 框架加载核心函数
	 * 使用方法：$this->load($class_name, $type)
	 * @param string $class_name 类名称
	 * @param string $type 类别
	 */
	protected static function load($classname) {
        if (!isset(self::$instance['loadclass'][ $classname ])) {
        	if (!class_exists($classname)) {
        	    ChelerApi::throwError($classname . ' is not exist!');
        	}
        
        	$obj = new $classname;
        	self::$instance['loadclass'][$classname] = $obj;
        	return $obj;
        }
        return self::$instance['loadclass'][$classname];
	}
	
	/**
	 *	系统获取library下面的类
	 *  1. 通过$this->getLibrary($class) 就可以加载Library下面的类
	 *  2. 单例模式-通过load核心函数加载
	 *  全局使用方法：$this->getLibrary($class)
	 *  @param  string  $class_name  类名称
	 *  @return object
	 */
	public static function getLibrary($class) {
	    $fullClassName = '\ChelerApi\library\\L'.$class;
		return self::load($fullClassName);
	}
	
	/**
	 *	系统获取Util类函数
	 *  1. 通过$this->getUtil($class) 就可以加载Util下面的类
	 *  2. 单例模式-通过load核心函数加载
	 *  全局使用方法：$this->getUtil($class)
	 *  @param  string  $class_name  类名称
	 *  @return \ChelerApi\library
	 */
	public static function getUtil($class) {
	    $fullClassName = '\ChelerApi\util\\U'.$class;
		return self::load($fullClassName);
	}
	
	/**
	 * 获取缓存对象
	 * 全局使用方法：$this->getCache()
	 * @return \ChelerApi\cache\CMemcached
	 */
	public static function getCache() {
		if (!isset(self::$instance['_cache_'])) {
			$cache = self::load('\ChelerApi\cache\CMemcached');
			$cache->add_server(ChelerApi::getConfig('memcache'));
			self::$instance['_cache_'] = $cache;
		}
		return self::$instance['_cache_'];
	}
	
	/**
	 * 注册到框架全局可用变量
	 * @param string $name 变量名称
	 * @param val $value   变量值
	 */
	public function register_global($name, $value) {
		self::$instance['global'][$name] = $value;
		$this->$name = $value;
	}
	
	/**
	 * 运行全局变量
	 */
	private function run_register_global() {
		if (isset(self::$instance['global']) && !empty(self::$instance['global'])) {
			foreach (self::$instance['global'] as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}