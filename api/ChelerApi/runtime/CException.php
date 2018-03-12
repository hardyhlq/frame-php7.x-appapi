<?php
namespace ChelerApi\runtime;

/**
 * 框架异常类
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class CException extends \Exception{
	
	/**
	 * 异常模板
	 * @param $e
	 */
	public static function errorTpl($e) {
		$conf = \ChelerApi\ChelerApi::getConfig();

		if (!$conf['debug'] && $e->code == 10000) {
			$msg = '系统繁忙，请稍后再试';
		}
		
		//如果debug关闭，则不显示debug错误信息
		if (!$conf['debug']) {
			return \ChelerApi\ChelerApi::return500();
		}
		//网页500
		header('HTTP/1.1 500 Internal Server Error');
		header("status: 500 Internal Server Error");
		$runTrace = $e->getTrace();
		krsort($runTrace);
		$traceMessage = [];
		$k = 1;
		foreach ($runTrace as $v) {
			$traceMessage[] = [
			    'id'=>$k,
			    'file' => $v['file'],
			    'line' => $v['line'],
			    'code' => self::getLineCode($v['file'], $v['line'])
		     ];
			$k++;
		}
		unset($k);unset($runTrace);
		if ($e->url) {
		    echo json_encode([
		        'code'=>500,
		        'message'=>$e->message,
		        'url' => $e->url,
		        'data' => $e->data,
		        'trace'=>$traceMessage
		    ]);
		}else{
		  echo json_encode(['code'=>500, 'message'=>$e->message, 'trace'=>$traceMessage]);
		}
		
		exit;
	}
	
	/**
	 *
	 * get error file line code
	 * @param string $file
	 * @param int $line
	 * @return string
	 */
	private static function getLineCode($file,$line) {
		$fp = fopen($file,'r');
		$i = 0;
		while(!feof($fp)) {
			$i++;
			$c = fgets($fp);
			if($i==$line) {
				return $c;
				break;
			}
		}
	}
}