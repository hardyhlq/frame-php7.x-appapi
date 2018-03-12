<?php
namespace ChelerApi\dao\db;

/**
 * SQL 语句组装
 * @author lonphy
 */
trait TSqlMaker {

	/**
	 * 组装INSERT语句
	 * 
	 * @param array $val 组装的数组 [ k => v, ...]
	 * @return string "(`key`) VALUES ('value')"
	 */
	public function makeInsert(array $val) {
		if (empty($val)) {
		    return '';
		}
		
		return \sprintf(' ( %s ) VALUES ( %s ) ',
		          $this->makeImplode(\array_keys($val), true),
		          $this->makeImplode($val)
		);
	}
	
	/**
	 * 组装批量INSERT语句
	 * 返回：('key') VALUES ('value'),('value2')
	 * 
	 * @param array $val 组装的数组 [ [k1=>v1, ...], [k1=v11, ...], ...]
	 * @return string "(`k1`, `k2`, ...) VALUES ('v1', 'v2', ...), ('v11',...)"
	 */
	public function makeInserts($field, $data) {
	    $field = ' (' . $this->makeImplode($field, 1) . ') '; //字段组装
	    $temp_data = array();
	    $data = (array) $data;
	    foreach ($data as $val) {
	        $temp_data[] = '(' . $this->makeImplode($val) . ')';
	    }
	    $temp_data = implode(',', $temp_data);
	    return $field . ' VALUES ' . $temp_data;
	}
	
	/**
	 * 组装UPDATE语句
	 * 
	 * @param  array $val 组装的数组  [k0 => v0, k1 => v1, ...]
	 * @return string " SET `k0` = 'v0', `k1` = 'v1', ... "
	 */
	public function makeUpdate(array $val) {
		if ( empty($val) ) {
		    return '';
		}
		$tmp = [];
		
		foreach ($val as $k => $v) {
			if (\is_array($v)) {
				$ktmp = $this->escape($k, 1);
				if(\is_assoc($v)) {
					foreach($v as $op => $value) {
						$temp[] = $ktmp .' = '. $ktmp .' '. $op .' '. $this->escape($value);
					}
				}
			}else{
				$temp[] = $this->makeKV($k, $v);
			}
		}
		return 'SET ' . implode(',', $temp);
	}
	
	/**
	 * 组装LIMIT语句
	 * @param  int $start 开始
	 * @param  int $num   条数
	 * @return string " LIMIT $start, $num "
	 */
	public function makeLimit($start, $num = NULL) {
		$start = (int) $start;
		$start = ($start < 0) ? 0 : $start;
		if ($num === NULL) {
			return 'LIMIT ' . $start;
		} else {
			return sprintf(' LIMIT %u, %u ', $start, abs((int) $num));
		}
	}
	
	/**
	 * 组装IN语句
	 * @param  array $val 数组值  如：array(1,2,3)
	 * @return string " IN ( '1', '2', ... ) "
	 */
	public function makeIn(array $val) {
		return sprintf(' IN ( %s ) ', $this->makeImplode($val));
	}
	
	/**
	 * 组装WHERE语句
	 * @param array $val array('key' => 'val')
	 * @return string
	 */
	public function makeWhere(array $val) {
		if ( empty($val) ) {
		    return '';
		}
		
		$temp = [];
		foreach ($val as $k => $v) {
			if (\is_array($v)) {
				$k = $this->escape($k, true);
				
				if(\is_assoc($v)) {
					foreach($v as $op => $value) {
					    if(\is_array($value)){
    						$temp[] = sprintf(' %s %s (%s) ', $k, $op, $this->makeImplode($value) );
					    }else {
    						$temp[] = sprintf(' %s %s %s ', $k, $op, $this->escape($value) );
					    }
					}	
				} else {
					$temp[] = $k . $this->makeIn($v);
				}
			}else{
				$temp[] = $this->makeKV($k, $v);
			}
		}
		return ' WHERE ' . \implode(' AND ', $temp);
	}
	
	/**
	 * 单个或数组参数过滤
	 * @param  string|array $val 需要过滤的数据
	 * @param  bool $iskey 是否是字段名, 默认false
	 * @return string
	 */
	public function escape($val, $iskey = false) {
		if (\is_array($val)) {
			foreach ($val as &$v) {
				$v = \trim($this->escapeQuotes($v, $iskey));
			}
			return $val;
		}
		
		return $this->escapeQuotes($val, $iskey);
	}
	
	/**
	 * 组装键值对
	 * @param  string $k KEY值
	 * @param  string $v VALUE值
	 * @return string "`k` = 'v'"
	 */
	public function makeKV($k, $v) {
		return $this->escape($k, true) . ' = ' . $this->escape($v);
	}
	
	/**
	 * SQL组装-将数组值通过，隔开
	 * 返回：'1','2','3'
     * DAO中使用方法：$this->dao->db->build_implode($val, $iskey = 0)
	 * @param  array $val   值
	 * @param  int   $iskey 0-过滤value值，1-过滤字段
	 * @return string 
	 */
	public function makeImplode($val, $iskey = 0) {
		if (!is_array($val) || empty($val)) return '';
		return implode(',', $this->escape($val, $iskey));
	}
	
	/**
	 * SQL组装-检查DAO中进来的数组参数是否key键存在
	 * @param array $data  例如：array("username" => 'asdasd')
	 * @param string $fields  例如："username,password"
	 */
	public function makeKey(array $data, array $fields) {
		$fields = \explode(',', $fields);
		$temp = array();
		foreach ($data as $key => $value) {
			if (\in_array($key, $fields)) {
				$temp[$key] = $value;
			}
		}
		return $temp;
	}

	public function quote($str, $noarray = false) {
		if (is_string($str))
			return '\'' . addcslashes($str, "\n\r\\'\"\032") . '\'';
	
		if (is_int($str) or is_float($str))
			return '\'' . $str . '\'';
	
		if (is_array($str)) {
			if($noarray === false) {
				foreach ($str as &$v) {
					$v = self::quote($v, true);
				}
				return $str;
			} else {
				return '\'\'';
			}
		}
	
		if (is_bool($str))
			return $str ? '1' : '0';
	
		return '\'\'';
	}

	/**
	 * SQL过滤
	 * 
	 * @param  string $val 过滤的值
	 * @param  int    $iskey 0-过滤value值，1-过滤字段
	 * @return string
	 */
	private function escapeQuotes($val, $iskey = false) {
		if ($iskey) {
		    $val = \str_replace(['`', ' '], '', $val);
		    return ' `'.\addslashes(stripslashes($val)).'` ';
		}
		return " '" .$val . "' ";
	}
}