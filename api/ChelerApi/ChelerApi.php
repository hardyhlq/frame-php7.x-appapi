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
	    
	    // controller路径命名空间注册
	    $path = self::getConfig('controller.path');
	    $path = self::getAppPath($path);
	    CClassLoader::registerNamespace('controller', $path);
	    
	    // 本地Service路径命名空间注册
	    $path = self::getConfig('localService.path');
	    $path = self::getAppPath($path);
	    CClassLoader::registerNamespace('localService', $path);
	    
	    unset($path);
	    CClassLoader::register();
	    
	    // 初始化远程服务地址
	    \ChelerApi\runtime\CRpcService::init();
	}
	
	/**
	 * 框架启动函数
	 */
	public static function run() {
	    self::setupLoader();
	    
		try {
		    // 路由分发
			$router = ChelerApi::loadclass('ChelerApi\runtime\CRouter');
			$router->router();
			
			// 路由执行
			$routerExec = ChelerApi::loadclass('ChelerApi\runtime\CRouterExec');
			$routerExec->exec();
		}
		catch (\ChelerApi\runtime\CException $e) {
			CException::errorTpl($e);
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
	 * XSS过滤，输出内容过滤
	 * 全局使用方法：ChelerApi::output($string, $type = 'encode');
	 * @param string $string  需要过滤的字符串
	 * @param string $type    encode HTML处理 | decode 反处理
	 * @return string
	 */
	public static function output($string, $type = 'encode') {
		$html = array("&", '"', "'", "<", ">", "%3C", "%3E");
		$html_code = array("&amp;", "&quot;", "&#039;", "&lt;", "&gt;", "&lt;", "&gt;");
		if ($type == 'encode') {
			if (function_exists('htmlspecialchars')) return htmlspecialchars($string);
			$str = str_replace($html, $html_code, $string);
		} else {
			if (function_exists('htmlspecialchars_decode')) return htmlspecialchars_decode($string);
			$str = str_replace($html_code, $html, $string);
		}
		return $str;
	}

	/**
	 * 组装URL
	 * default：index.php?m=user&c=index&a=run
	 * rewrite：/user/index/run/?id=100
	 * path: /user/index/run/id/100
	 * html: user-index-run.htm?uid=100
	 * 全局使用方法：ChelerApi::url('user|delete', array('id' => 100))
	 * @param String $action  m,c,a参数，一般写成 cms|user|add 这样的m|c|a结构
	 * @param array  $params  URL中其它参数
	 * @param String $baseUrl 是否有默认URL，如果有，则
	 */
	public static function url($action, $params = [], $baseUrl = '') {
		$conf = ChelerApi::getConfig(['uriType', 'url', 'ismodule']);
		$action = explode('|', $action);
		
		$baseUrl = ($baseUrl == '') ? rtrim($conf['url'], '/') . '/' : $baseUrl;
		$ismodule = ChelerApi::getConfig('ismodule');
		switch ($conf['uriType']) {

			case 'rewrite' :
				$actionStr = implode('/', $action);
				$paramsStr = '';
				if ($params) {
					$paramsStr = '?' . http_build_query($params);
				}
				return $baseUrl . $actionStr . $paramsStr;
					
			default:
				$actionStr = '';
				if ($ismodule === true) {
					$actionStr .= 'm=' . $action[0];
					$actionStr .= '&c=' . $action[1];
					$actionStr .= '&a=' . $action[2] . '&';
				} else {
					$actionStr .= 'c=' . $action[0];
					$actionStr .= '&a=' . $action[1] . '&';
				}
				$actionStr = '?' . $actionStr;

				
		}
		
		$paramsStr = '';
		if ($params) {
		    $paramsStr = http_build_query($params);
		}
		
		return $baseUrl . $actionStr . $paramsStr;
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
	 * 【静态】RPC服务层
	 * 全局使用方法：ChelerApi::getRPCService($servicename, $version)
	 * @param string $servicename 完整服务名 包名.服务
	 * @param integer $version 版本, 默认为1
	 */
	public static function getRPCService($servicename, $path='b') {
	    static $objs = [];
	    $hash = md5($servicename);
	    if (!isset($objs[$hash])) {
	        $objs[$hash] = new \ChelerApi\runtime\CRpcService($servicename, $path);
	    }
	    
	    return $objs[$hash];
	}
	
	/**
	 * 【静态】获取本地服务层
	 * @param string $serviceName 服务层名称
	 * @return ChelerApi\runtime\CLocalService
	 */
	public static function getLocalService($serviceName) {
	    static $objs = [];
	    $hash = md5($serviceName);
	    if (!isset($objs[$hash])) {
	        $class_name = '\\localService\\'.$serviceName .'Service';
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
class Controller extends \ChelerApi\runtime\CCore {

    /**
     * @var \ChelerApi\controller\CController
     */
    protected $controller;

    /**
     * 初始化
     */
    public function __construct() {
        parent::__construct();
        $this->controller = $this->load('\ChelerApi\controller\CController'); //导入Controller
    }
    
    /**
	 *	控制器 api输出
	 *  Controller中使用方法：$this->apiReturn()
	 * 	@param  int     $status  0:错误信息|1:正确信息
	 * 	@param  string  $message  显示的信息
	 * 	@param  array   $data    传输的信息
	 *  @return object
	 */
	private function apiReturn($status,$message = '', $data = []) {
	    $version = 1;
	    //TODO:版本获取
	    $uri = $this->controller->getUri();
	    $pos = strpos($uri, '?');
		$return_data = [
		    'code' => $status,
		    'data' => $data,
		    'msg' => $message,
		    'version' => API_VERSION,
		    'request' => $pos===false ? $uri: substr($uri, 0, $pos)
		];
		exit(json_encode($return_data));
	}
	
	/**
	 * API成功返回
	 * @param int $code
	 * @param string $message
	 * @param mixed $data
	 */
	protected function apiSuccess($code, $message='', $data=[]) {
	    $this->apiReturn($code, $message, $data);
	}
	
	/**
	 * API失败返回
	 * @param int $code
	 * @param string $message
	 * @param mixed $data
	 */
	protected function apiError($code, $message='', $data=[]) {
	    $this->apiReturn($code,$message, $data);
	}
}