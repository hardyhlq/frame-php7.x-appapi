<?php
namespace ChelerApi\runtime;

/**
 * 远程服务异常类
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class CRpcException extends CException{
	function __construct($message, $code, $url, $data='') {
	    parent::__construct($message, $code);
	    $this->url = $url;
	    $this->data = $data;
	}
}