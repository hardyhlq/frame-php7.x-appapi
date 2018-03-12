<?php
namespace ChelerApi\util;
/**
 * 调试
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class Udebug {

	public static $mark_arr = []; //存放时间的静态变量
	
	/**
	 * debug-BUG调试工具-打印出信息
	 * 使用方法：$this->getUtil('debug')->dump($data, $isexit = 0)
	 * @param  string  $data   参数
	 * @param  int     $isexit 是否跳出
	 * @return 
	 */
	public function dump($data, $isexit = 0) {
	
		ob_start();
        var_dump($data);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
		echo $output;
		if ($isexit) exit();

	}
	
	/**
	 * debug-BUG调试工具-程序标记
	 * 使用方法：$this->getUtil('debug')->mark($name)
	 * @param  string  $name 开始和结束时间的标记名称
	 * @return 
	 */
	public function mark($name) {
		self::$mark_arr['time'][$name][] = microtime(TRUE);
		self::$mark_arr['memory'][$name][] = memory_get_usage();
		return self::$mark_arr;
	}
	
	/**
	 * debug-BUG调试工具-计算程序段使用的时间
	 * 使用方法：$this->getUtil('debug')->use_time($name, $decimal = 6)
	 * @param  string  $name 开始和结束时间的标记名称
	 * @param  string  $decimal 小数位数
	 * @return 
	 */
	public function use_time($name, $decimal = 6) {
		if (!isset(self::$mark_arr['time'][$name][1])) {
			self::$mark_arr['time'][$name][1] = microtime(TRUE);
		}
		return number_format(self::$mark_arr['time'][$name][1] - self::$mark_arr['time'][$name][0], $decimal);
	}
	
	/**
	 * debug-BUG调试工具-计算程序段计算内存使用峰值
	 * 使用方法：$this->getUtil('debug')->use_memory($name)
	 * @param  string  $name 开始和结束时间的标记名称
	 * @return 
	 */
	public function use_memory($name) {
		if (!isset(self::$mark_arr['memory'][$name][1])) {
			self::$mark_arr['memory'][$name][1] = memory_get_usage();
		}
		return number_format(self::$mark_arr['memory'][$name][1] - self::$mark_arr['memory'][$name][0]);
	}
	
}
