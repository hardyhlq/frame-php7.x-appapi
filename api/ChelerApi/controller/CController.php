<?php
namespace ChelerApi\controller;
/**
 * 控制器基类
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class CController {
    use TRequest;
    use TValidate;
    use TFilter;
	
	/**
	 * 初始化控制器，
	 */
	public function __construct() {
		$this->filter(); //全局过滤函数，对GET POST数据自动过，InitPHP采取非常严格的数据过滤机制
		$this->setToken(); //生成全局TOKEN值，防止CRsf攻击
	}
	
	/**
	 *	控制器 重定向
	 *  Controller中使用方法：$this->controller->redirect($url, $time = 0)
	 * 	@param  string  $url   跳转的URL路径
	 * 	@param  int     $time  多少秒后跳转
	 *  @return 
	 */
	public function redirect($url, $time = 0) {
		if (!headers_sent()) {
			if ($time === 0) header("Location: ".$url);
			header("refresh:" . $time . ";url=" .$url. "");
		} else {
			exit("<meta http-equiv='Refresh' content='" . $time . ";URL=" .$url. "'>");
		}
	}
	
	/**
	 *	返回404
	 *  Controller中使用方法：$this->controller->return404()
	 *  @return 
	 */
	public function return404() {
   		header('HTTP/1.1 404 Not Found');
    	header("status: 404 Not Found"); 
		return;
	}
	
	/**
	 *	返回404
	 *  Controller中使用方法：$this->controller->return200()
	 *  @return 
	 */
	public function return200() {
		header("HTTP/1.1 200 OK"); 
		return;
	}
	
	/**
	 *	返回500
	 *  Controller中使用方法：$this->controller->return500()
	 *  @return 
	 */
	public function return500() {
		header('HTTP/1.1 500 Internal Server Error');
		return;
	}
	
	/**
	 *	返回403
	 *  Controller中使用方法：$this->controller->return403()
	 *  @return 
	 */
	public function return403() {
		header('HTTP/1.1 403 Forbidden');
		return;
	}
	
	/**
	 *	返回405
	 *  Controller中使用方法：$this->controller->return405()
	 *  @return 
	 */
	public function return405() {
		header('HTTP/1.1 405 Method Not Allowed');
		return;
	}
		
	/**
	 *	类加载-获取全局TOKEN，防止CSRF攻击
	 *  Controller中使用方法：$this->controller->getToken()
	 *  @return 
	 */
	public function getToken() {
		return $_COOKIE['_token_'];
	}
	
	/**
	 *	类加载-检测token值
	 *  Controller中使用方法：$this->controller->checkToken($ispost = true)
	 *  @return 
	 */
	public function checkToken($ispost = true) {
		if ($ispost && !$this->isMethodPost()) return false;
		if ($this->getParameter('_token_') != $this->getToken()) return false;
		return true;
	}
	
	/**
	 *	类加载-设置全局TOKEN，防止CSRF攻击
	 *  Controller中使用方法：$this->controller->setToken()
	 *  @return 
	 */
	private function setToken() {
		if (!isset($_COOKIE['_token_']) ) {
			$str = substr(md5(time(). $this->getUserAgent()), 5, 8);
			setcookie('_token_', $str, NULL, '/');
			$_COOKIE['_token_'] = $str;	
		}
	}
}
