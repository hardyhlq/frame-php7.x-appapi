<?php
namespace ChelerApi\runtime;
use ChelerApi\ChelerApi;
/**
 * 路由执行器
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class CRouterExec {

	private $controller_postfix     = 'Controller'; //控制器后缀
	private $action_postfix         = ''; //动作后缀
	private $default_controller     = 'index'; //默认执行的控制器名称
	private $default_action         = 'run'; //默认执行动作名称
	private $default_module         = 'index';
	private $module_list            = ['index'];
	private $default_before_action  = 'before';//默认的前置Action
	private $default_after_action   = 'after'; //默认的后置Action


	/**
	 * 框架运行核心函数
	 * 1. 设置参数
	 * 2. 获取controller
	 * 3. 运行前置Action
	 * 4. 运行正常Action
	 * 5. 运行后置Action
	 * @return file
	 */
	public function exec() {
		$conf = ChelerApi::getConfig('controller'); //全局配置
		$this->init2Ehandle(); //收集错误信息，并记录
		$this->filter();
		$this->set_params($conf);
		//验证方法是否合法，如果请求参数不正确，则直接返回404
		$controllerObj = $this->checkRequest();
		$this->run_before_action($controllerObj);//前置Action
		$this->run_action($controllerObj); //正常流程Action
		$this->run_after_action($controllerObj); //后置Action
	}

	/**
	 * 验证请求是否合法
	 * 1. 如果请求参数m,c,a都为空，则走默认的
	 */
	private function checkRequest() {
		
		$controller  = isset($_GET['c']) ? trim($_GET['c']) : '';
		$action = isset($_GET['a']) ? trim($_GET['a']) : '';
		$client_version = isset($_GET['_v']) ? trim($_GET['_v']) : '';
		
		$conf = ChelerApi::getConfig('apiVersion');
		$conf = array_reverse($conf, 1);
		
		if ($controller == '' && $action == '' && $client_version == '') {
			$controller = $_GET['c'] = $this->default_controller;
			$action = $_GET['a'] = $this->default_action;
			$client_version = $_GET['_v'] = key($conf);// 默认最新版本
		}

		//controller处理，如果导入Controller文件失败，则返回404
		$path = '\controller\\';
		$controllerClass = $controller . $this->controller_postfix;

		$controllerObj = null;
		foreach ($conf as $version => $val) {

		    if ( intval(ltrim($version, 'v')) > intval(ltrim($client_version, 'v')) ) {
		        continue; // 如果有客户端的版本， 则以客户端版本为准
		    }
		    
		    // 通过配置数组查找
		    foreach ($val as $c => $as) {
		        if ($c == $controller && in_array($action, $as)) {
		            $controllerFullPath = $path . $version .'\\' . $controllerClass;
		            $controllerObj = ChelerApi::loadclass($controllerFullPath);
		            define('API_VERSION', $version);
		            goto END;
		        }
		    }
		    /*
		    // 通过反射查找
		    $controllerFullPath = $path . $version .'\\' . $controllerClass;
		    if(class_exists($controllerFullPath, 1)) { // 检查控制器版本
		        if (!method_exists($controllerFullPath, $action) ){ // 方法不存在，则继续查找前一个版本
		            continue;
		        }
		        define('API_VERSION', $version);
		        $controllerObj = ChelerApi::loadclass($controllerFullPath);
		        goto END;
		    }
		    */
	    }
	    
	    // 未找到返回404
	    if ( is_null($controllerObj) ) {
	        ChelerApi::return404();
	    }
	    
	    END:
		return $controllerObj;
	}

	/**
	 * 框架运行控制器中的Action函数
	 * 1. 获取Action中的a参数
	 * 2. 检测是否在白名单中，不在则选择默认的
	 * 3. 检测方法是否存在，不存在则运行默认的
	 * 4. 运行函数
	 * @param object $controller 控制器对象
	 * @return file
	 */
	private function run_action($controller) {
		$action = trim($_GET['a']);
		if (!method_exists($controller, $action)) {
			ChelerApi::throwError('Can not find default method : ' . $action);
		}

		$controller->$action();
	}

	/**
	 * 运行框架前置类
	 * 1. 检测方法是否存在，不存在则运行默认的
	 * 2. 运行函数
	 * @param object $controller 控制器对象
	 * @return file
	 */
	private function run_before_action($controller) {
		$before_action = $this->default_before_action . $this->action_postfix;
		if (!method_exists($controller, $before_action)) return false;
		$controller->$before_action();
	}

	/**
	 * 运行框架后置类
	 * 1. 检测方法是否存在，不存在则运行默认的
	 * 2. 运行函数
	 * @param object $controller 控制器对象
	 * @return file
	 */
	private function run_after_action($controller) {
		$after_action = $this->default_after_action . $this->action_postfix;
		if (!method_exists($controller, $after_action)) return false;
		$controller->$after_action();
	}

	/**
	 *	设置框架运行参数
	 *  @param  string  $params
	 *  @return string
	 */
	private function set_params($params) {
		if (isset($params['controller_postfix']))
		$this->controller_postfix = $params['controller_postfix'];
		if (isset($params['action_postfix']))
		$this->action_postfix = $params['action_postfix'];
		if (isset($params['default_controller']))
		$this->default_controller = $params['default_controller'];
		if (isset($params['default_module']))
		$this->default_module = $params['default_module'];
		if (isset($params['module_list']))
		$this->module_list = $params['module_list'];
		if (isset($params['default_action']))
		$this->default_action = $params['default_action'];
		if (isset($params['default_before_action']))
		$this->default_before_action = $params['default_before_action'];
		if (isset($params['default_after_action']))
		$this->default_after_action = $params['default_after_action'];
	}

	/**
	 *	m-c-a数据处理
	 *  @return string
	 */
	private function filter() {
		if (isset($_GET['m'])) {
			if (!$this->_filter($_GET['m'])) unset($_GET['m']);
		}
		if (isset($_GET['c'])) {
			if (!$this->_filter($_GET['c'])) unset($_GET['c']);
		}
		if (isset($_GET['a'])) {
			if (!$this->_filter($_GET['a'])) unset($_GET['a']);
		}
	}

	private function _filter($str) {
		return preg_match('/^[A-Za-z0-9_]+$/', trim($str));
	}

	/*
	 * 初始化异常和错误处理函数
	 * */
	public function init2Ehandle(){
		set_exception_handler(array($this,'handleException'));
		set_error_handler(array($this,'handleError'),error_reporting());
	}

	/*
	 *设置异常处理函数,写入日志文件
	 */
	public function handleException($exception){
		restore_exception_handler();
		\ChelerApi\runtime\CException::errorTpl($exception);
	}

	/*
	 *设置PHP错误处理回调函数,写入日志文件
	 */
	public function handleError($errorCode, $msg = '', $errorFile = 'unkwon', $errorLine = 0){
		$conf = ChelerApi::getConfig();
		restore_error_handler();
		if($conf['debug'] == true) {
			exit($msg);
		} else {
			return ChelerApi::return500();
		}
	}
}