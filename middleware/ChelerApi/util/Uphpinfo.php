<?php
namespace ChelerApi\util;
/**
 * phpinfo
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class Uphpinfo {
	
	/**
	 * 显示PHPINFO信息
	 * 使用方法：$this->getUtil('phpinfo')->get_phpinfo();
	 */
	public function get_phpinfo() {
		phpinfo();  
		exit;
	}
}