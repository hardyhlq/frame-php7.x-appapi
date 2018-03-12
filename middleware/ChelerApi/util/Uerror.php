<?php
namespace ChelerApi\util;
/**
 * 错误处理
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class Uerror {
	
	private $error_data = []; //error容器
	private $error_type = ['html', 'text', 'json', 'xml', 'array']; //error类型数组
	
	/**
	 *	Error机制 添加一个error
	 *  添加错误信息，不会直接输出，直到调用send_error的时候才会输出所有的错误信息
	 *  使用方法：$this->getUtil('error')->add_error('aaaa');
	 * 	@param  string   $error_message  错误信息
	 *  @return object
	 */
	public function add_error($error_message) {
		$this->error_data[] = $error_message;
	}
	
	/**
	 *	Error机制 输出一个error
	 *  输出错误信息，可以选择各种输出方式，json，xml，json,html
	 *  add_error的错误信息也会被一起输出
	 *	使用方法：$this->getUtil('error')->send_error();
	 * 	@param  string   $error_message  错误信息
	 * 	@param  string   $error_type     错误类型
	 *  @return object
	 */
	public function send_error($error_message, $error_type = 'json') {
		$this->error_data[] = $error_message;
		$error_type = strtolower($error_type);
		if (!in_array($error_type, $this->error_type)) $error_type = 'json';
		$this->display($error_type);
	}
	
	/**
	 *	Error机制 私有函数，error输出
	 * 	@param  string   $error_type     错误类型
	 *  @return object
	 */
	private function display($error_type) {
		if ($error_type == 'text') {
			$error = implode("\r\t", $this->error_data);
			exit($error);
		} elseif ($error_type == 'json') {
			exit(json_encode($this->error_data));
		} elseif ($error_type == 'xml') {
			$xml = '<?xml version="1.0" encoding="utf-8"?>';
			$xml .= '<return>';
				foreach ($this->error_data as $v) {
					$xml .= '<error>' .$v. '</error>';
				}
			$xml .= '</return>';
		 	exit($xml);
		} elseif ($error_type == 'array') {
			exit(var_export($this->error_data));
		}
	}
}