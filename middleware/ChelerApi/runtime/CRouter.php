<?php
namespace ChelerApi\runtime;
use ChelerApi\ChelerApi;
/**
 * 路由
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class CRouter {

    /**
     * 路由分发-路由分发核心函数
     */
    public function router() {
        $res = \explode('.', $_GET['m']);
        unset($_GET['m']);
        $len = \count($res);
        $type = $res[0] === 'l' ? 'LogicService' : 'BaseService';
        $_GET['method'] = $res[$len - 1];
        $namespace = '\\modules\\' . $type . '\\' . \implode('\\\\', \array_slice($res, 1, $len - 3));
        $_GET['class'] = $namespace . '\\' . $res[$len - 2] . 'Service';
        
        if (isset($_POST['args'])) {
            $str = \str_replace(' ', '+', $_POST['args']);
            $str = \base64_decode($str);
            $_POST['args'] = \unserialize($str);
        } else {
            $_POST['args'] = null;
        }
    }
    
	/**
	 * 框架运行核心函数
	 */
	public function exec() {
		$this->init2Ehandle(); //收集错误信息，并记录
		
		//验证方法是否合法，如果请求参数不正确，则直接返回404
		$serviceObj = $this->checkRequest();

		$this->run_method($serviceObj); //调用方法
	}

	/**
	 * 验证请求是否合法
	 */
	private function checkRequest() {
	    
	    $serviceClass = $_GET['class'];
	    $serviceObj = ChelerApi::loadclass($serviceClass);
	    // 未找到返回404
	    if ( is_null($serviceObj) ) {
	        ChelerApi::return404();
	    }
	    
		return $serviceObj;
	}

	/**
	 * 运行服务层中的Action函数
	 */
	private function run_method($service) {
		$method = trim($_GET['method']);
		
		if (!method_exists($service, $method)) {
			ChelerApi::throwError('Can not find method : ' . $method);
		}
		
		$ref = new \ReflectionClass(\get_class($service));
		$refMethod = $ref->getMethod($method);
		$params_total = $refMethod->getNumberOfParameters();//所有参数个数
		$params_required = $refMethod->getNumberOfRequiredParameters();//必填参数个数

		
		$args = $_POST['args'];
		//判断必填参数个数
		$rel_count = \count($args);
		if($rel_count < $params_required){
		    ChelerApi::throwError($params_required .' params required,  : ' .$rel_count. ' gived call '. $method);
		}
		if ( $args === NULL ) {
		    $args = [];
		}
		$data = $refMethod->invokeArgs($service, $args);
		$result = [];
		$result['code'] = 200;
		$result['data'] = $data;
		exit(json_encode($result));
	}

	/*
	 * 初始化异常和错误处理函数
	 * */
	public function init2Ehandle(){
		\set_exception_handler(array($this,'handleException'));
		\set_error_handler(array($this,'handleError'),error_reporting());
	}

	/*
	 *设置异常处理函数,写入日志文件
	 */
	public function handleException($exception){
		\restore_exception_handler();
		\ChelerApi\runtime\CException::errorTpl($exception);
	}

	/*
	 *设置PHP错误处理回调函数,写入日志文件
	 */
	public function handleError($errorCode, $msg = '', $errorFile = 'unkwon', $errorLine = 0){
		$debug = ChelerApi::getConfig('debug');
		\restore_error_handler();
		if($debug == true) {
			exit($msg);
		} else {
			return ChelerApi::return500();
		}
	}
}