<?php 
namespace ChelerApi\runtime;

/**
 * 框架路由
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class CRouter {
	
	/**
	 * 路由分发-路由分发核心函数 
	 */
	public function router() {
		$uriType = \ChelerApi\ChelerApi::getConfig('uriType');
		
		switch ($uriType) {
			case 'rewrite' :
				$request = $this->getRequest();
				$this->parseRewriteUri($request);
				break;
			default :
				return false;
		}
		return true;
	}
	
	/**
	 * 路由分发，获取Uri数据参数
	 * 1. 对Service变量中的uri进行过滤
	 * 2. 配合全局站点url处理request
	 * @return string
	 */
	private function getRequest() {
		$url = \ChelerApi\ChelerApi::getConfig('url');
		$filter_param = ['<','>','"',"'",'%3C','%3E','%22','%27','%3c','%3e'];
		$uri = str_replace($filter_param, '', $_SERVER['REQUEST_URI']);
	    $posi = strpos($uri, '?');
    	if ($posi) $uri = substr($uri,0,$posi);
    	$urlArr = parse_url($url);
		$request = str_replace(trim($urlArr['path'], '/'),'', $uri);
		if (strpos($request, '.php')) {
			$request = explode('.php', $request);
			$request = $request[1];
		}
		return $request;
	}
	
	/**
	 * 解析rewrite方式的路由
	 * 1. 解析index.php/user/new/username/?id=100
	 * 2. 解析成数组，array()
	 * @param string $request
	 */
	private function parseRewriteUri($request) {
		if (!$request) return false;
		$request =  trim($request, '/');
		if ($request == '') return false;
		$request =  explode('/', $request);
		if (!is_array($request) || count($request) !== 3) return false;
		if (isset($request[0])) $_GET['_v'] = $request[0]; // 版本
		if (isset($request[1])) $_GET['c'] = $request[1];
		if (isset($request[2])) $_GET['a'] = $request[2];
		return $request;
	}

}