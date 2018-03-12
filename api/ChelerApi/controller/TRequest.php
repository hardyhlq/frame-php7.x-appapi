<?php
namespace ChelerApi\controller;

/**
 * Controller-request Http请求
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
trait TRequest {
	
	/**
	 * 安全过滤类-获取GET或者POST的参数值，经过过滤
	 * 如果不指定$type类型，则获取同名的，POST优先
	 * $isfilter 默认开启，强制转换请求的数据
	 * 该方法在Controller层中，获取所有GET或者POST数据，都需要走这个接口
	 *  Controller中使用方法：$this->controller->get_gp($value, $type = null,  $isfilter = true)
	 * @param  string|array $value 参数
	 * @param  string|array $type 获取GET或者POST参数，P - POST ， G - GET, U - PUT , D -DEL
	 * @param  bool         $isfilter 变量是否过滤
	 * @return string|array
	 */
	public function get_gp($value, $type = null,  $isfilter = false) {
		//GET处理
		foreach ($_GET as $key=>$getVal){
			$key1 = str_replace("amp;", "", $key);
			unset($_GET[$key]);
			$_GET[$key1] = $getVal;
		}
		//GET处理
		if ($type == 'U' || $type == 'D') {
			parse_str(file_get_contents('php://input'), $requestData);
		}
		if (!is_array($value)) {
			$temp = null;
			if ($type === null) {
				if (isset($_GET[$value])) $temp = $_GET[$value];
				if (isset($_POST[$value])) $temp = $_POST[$value];
			} elseif ($type == 'U' || $type == 'D') { //PUT 和 DEL
				$temp = $requestData[$value];
			} else {
				$temp = (strtoupper($type) == 'G') ? $_GET[$value] : $_POST[$value];
			}
			$temp = ($isfilter === true) ? $this->filter_escape($temp) : $temp;
			return $temp;
		} else {
			$temp = array();
			foreach ($value as $val) {
				if ($type === null) {
					if (isset($_GET[$val])) $temp[$val] = $_GET[$val];
					if (isset($_POST[$val])) $temp[$val] = $_POST[$val];
				} elseif ($type == 'U' || $type == 'D') {
					$temp[$val] = $requestData[$val];
				} else {
					$temp[$val] = (strtoupper($type) == 'G') ? $_GET[$val] : $_POST[$val];
				}
				
				if(isset($temp[$val]))
				{
					$temp[$val] = ($isfilter === true) ? $this->filter_escape($temp[$val]) : $temp[$val];
				}
			}
			return $temp;
		}
	}
	
	/**
	 * TRequest-获取COOKIE信息
	 *  Controller中使用方法：$this->controller->getCookie($name = '')
	 * @param  string $name COOKIE的键值名称
	 * @return string
	 */
	public function getCookie($name = '') {
		if ($name == '') return $_COOKIE;
		return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : '';
	}
	
	/**
	 * TRequest-获取SESSION信息
	 *  Controller中使用方法：$this->controller->getSession()
	 * @param  string $name SESSION的键值名称
	 * @return string
	 */
	public function getSession($name = '') {
		if ($name == '') return $_SESSION;
		return (isset($_SESSION[$name])) ? $_SESSION[$name] : '';
	}
	
	/**
	 * TRequest-获取ENV信息
	 *  Controller中使用方法：$this->controller->getEnv($name = '')
	 * @param  string $name ENV的键值名称
	 * @return string
	 */
	public function getEnv($name = '') {
		if ($name == '') return $_ENV;
		return (isset($_ENV[$name])) ? $_ENV[$name] : '';
	}
	
	/**
	 * TRequest-获取SERVER信息
	 *  Controller中使用方法：$this->controller->getServer()
	 * @param  string $name SERVER的键值名称
	 * @return string
	 */
	public function getServer($name = '') {
		if ($name == '') return $_SERVER;
		return (isset($_SERVER[$name])) ? $_SERVER[$name] : '';
	}
	
	/**
	 *	TRequest-获取当前正在执行脚本的文件名
	 *  Controller中使用方法：$this->controller->getPhpSelf()
	 *  @return string
	 */
	public function getPhpSelf() {
		return $this->getService('PHP_SELF');
	}
	
	/**
	 *	TRequest-获取当前正在执行脚本的文件
	 *  Controller中使用方法：$this->controller->getServerName()
	 *  @return string
	 */
	public function getServerName() {
		return $this->getServer('SERVER_NAME');
	}
	
	/**
	 *	TRequest-获取请求时间
	 *  Controller中使用方法：$this->controller->getRequestTime()
	 *  @return int
	 */
	public function getRequestTime() {
		return $this->getServer('REQUEST_TIME');
	}
	
	/**
	 * TRequest-获取useragent信息
	 *  Controller中使用方法：$this->controller->getUserAgent()
	 * @return string
	 */
	public function getUserAgent() {
		return $this->getServer('HTTP_USER_AGENT');	
	}	
	
	/**
	 * TRequest-获取URI信息
	 *  Controller中使用方法：$this->controller->getUri()
	 * @return string
	 */
	public function getUri() {
		return $this->getServer('REQUEST_URI');
	}
	
	/**
	 * TRequest-判断是否为POST方法提交
	 *  Controller中使用方法：$this->controller->isMethodPost()
	 * @return bool
	 */
	public function isMethodPost() {
		return (strtolower($this->getServer('REQUEST_METHOD')) === 'post');
	}
	
	/**
	 * TRequest-判断是否为GET方法提交
	 *  Controller中使用方法：$this->controller->isMethodGet()
	 * @return bool
	 */
	public function isMethodGet() {
		return (strtolower($this->getServer('REQUEST_METHOD')) == 'get');
	}
	
	/**
	 * TRequest-判断是否为AJAX方式提交
	 *  Controller中使用方法：$this->controller->isMethodAjax()
	 * @return bool
	 */
	public function isMethodAjax() {
		if (
		    $this->getServer('HTTP_X_REQUESTED_WITH') && 
		    strtolower($this->getServer('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest'
		) {
		        return true;
		}
		return false;
	}

	/**
     * TRequest-获取用户IP
     * 
     * Controller中使用方法：<code>$this->controller->getIp()</code>
     * @return string
     */
    public function getIp() {
        static $realip = null;
        if (null !== $realip) {
            return $realip;
        }
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } else if (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        // 处理多层代理的情况
        if (false !== strpos($realip, ',')) {
            $realip = reset(explode(',', $realip));
        }
        // IP地址合法验证
        $realip = filter_var($realip, FILTER_VALIDATE_IP, null);
        if (false === $realip) {
            return '0.0.0.0';   // unknown
        }
        return $realip;
    }
}
