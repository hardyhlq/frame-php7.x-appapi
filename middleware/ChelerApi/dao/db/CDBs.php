<?php
namespace ChelerApi\dao\db;

use ChelerApi\ChelerApi;

class CDBs {
	protected static $dbs = [ ]; // 存储 driver，db对象
	
	/**
	 *
	 * @var \ChelerApi\dao\db\driver\IDriver
	 */
	protected $db = NULL;
	public $model = 'defualt'; // DB配置模型，默认为default
	
	/**
	 * 数据库初始化，DB切换入口
	 * 1.
	 * 可以在使用中通过$this->db('test')来切换数据库
	 * 2. 该函数是DB默认初始化入口
	 * 3. 支持多数据库链接，主从，随机分布式数据库
	 * 
	 * @param obj $db
	 */
	public function db($db = '') {
		$conf = ChelerApi::getConfig ( 'db' );
		$this->model = $db ?  : 'default'; // Db模型
		if (isset ( self::$dbs [$this->model] )) {
			return true;
		}
		
		if (! isset ( $conf [$this->model] )) {
			ChelerApi::throwError ( 'database confing model {' . $this->model . '} is error!' );
		}
		
		$db_type = $conf [$this->model] ['db_type'];
		$config = $conf [$this->model];
		
		switch ($db_type) {
			case 1 : // 主从模型
				$key = \floor ( \mt_rand ( 1, (\count ( $config ) - 2) ) );
				self::$dbs [$this->model] ['master'] ['linkid'] = $this->create ( $config [0] );
				self::$dbs [$this->model] ['salver'] ['linkid'] = $this->create ( $config [$key] );
				break;
			
			case 2 : // 随机模型
				$key = \floor ( \mt_rand ( 0, \count ( $config ) - 2 ) );
				self::$dbs [$this->model] ['linkid'] = $this->create ( $config [$key] );
				break;
			
			default : // 默认单机模型
				self::$dbs [$this->model] ['linkid'] = $this->create ( $config [0] );
		}
		return true;
	}
	
	/**
	 * 获取数据库链接资源
	 * 
	 * @param string $sql SQL语句进行分析
	 * @return object
	 */
	protected function getHandle($sql) {
		$conf = ChelerApi::getConfig ();
		$db_type = $conf ['db'] [$this->model] ['db_type'];
		if (isset ( $conf ['issqlcontrol'] ) && $conf ['issqlcontrol'] == 1) {
			$conf ['sqlcontrolarr'] [] = $sql;
			ChelerApi::setConfig ( 'sqlcontrolarr', $conf ['sqlcontrolarr'] );
		}
		if ($db_type == 1) { // 主从
			if ($this->is_insert ( $sql )) {
				$this->db->linkid = self::$dbs [$this->model] ['master'] ['linkid'];
			} else {
				$this->db->linkid = self::$dbs [$this->model] ['salver'] ['linkid'];
			}
		} else {
			$this->db->linkid = self::$dbs [$this->model] ['linkid'];
		}
		return $this->db->linkid;
	}
	
	/**
	 * 切换到默认数据库
	 * 
	 * @return boolean
	 */
	protected function setDefaultHandle() {
		if (isset ( self::$dbs ['default'] )) {
			$this->model = 'default';
			$this->db->linkid = self::$dbs [$this->model] ['linkid'];
			return true;
		}
		return false;
	}
	
	/**
	 * DB链接器，主要用来链接数据库
	 * 1.
	 * config 必要的参数：host、username、password、
	 * database、charset、pconnect
	 * 2. dirver 默认如果是mysql连接，可以不填写，如果填写了driver，则会使用不同的数据库类型，例如：mysqli
	 * 3. $this->db 是db类对象，单例
	 * 
	 * @param array $config
	 * @return object
	 */
	private function create($config) {
		$host = $config ['host'];
		$user = $config ['username'];
		$password = $config ['password'];
		$database = $config ['database'];
		$charset = $config ['charset'];
		if ($this->db === null) {
			$this->db = $this->get_driver (); // DB对象
		}
		return $this->db->init ( $host, $user, $password, $database, $charset );
	}
	
	/**
	 * 获取数组引擎对象
	 * 
	 * @param string $driver 暂时只支持mysql
	 * @return object
	 */
	private function get_driver($driver = 'Mysqli') {
		return ChelerApi::loadclass ( '\ChelerApi\dao\db\driver\C' . $driver );
	}
	
	/**
	 * SQL分析器
	 * 
	 * @param string $sql SQL语句
	 * @return bool
	 */
	private function is_insert($sql) {
		$sql = \trim ( $sql );
		$sql_temp = \strtoupper ( substr ( $sql, 0, 6 ) );
		if ($sql_temp === 'SELECT')
			return false;
		return true;
	}
	
	/**
	 * 按月分表-分库方法
	 * 1.
	 * 当数据表数据量过大的时候，可以根据按月分表的方法来进行分表
	 * 2. 按月分库会根据当前的时间来决定是几月份的数据
	 * 3. 按月分库$defaultId，可以自定义填入月份，例如：get_mon_table('test', 2),则返回 test_02
	 * Dao中使用方法：$this->dao->db->month_identify($tbl, $defaultId = '')
	 * 
	 * @param string $tbl
	 * @param string $defaultId
	 */
	public function month_identify($tbl, $defaultId = '') {
		if (empty ( $defaultId )) {
			$mon = \sprintf ( '%02d', \date ( 'm', ChelerApi::getTime () ) );
			return $tbl . '_' . $mon;
		} else {
			return $tbl . '_' . \sprintf ( '%02d', $defaultId );
		}
	}
	
	/**
	 * 根据数值来确定分表-分库方法
	 * 1.
	 * 可以自定义分表-分库的模板前缀$tbl变量
	 * 2. 可以自定义截取长度
	 * 3. 一般可以根据用户UID来获取分表或者分库
	 * Dao中使用方法：$this->dao->db->num_identify($num, $tbl, $default = 1)
	 * 
	 * @param int $num 数值
	 * @param string $tbl 模板前缀
	 * @param int $default 默认截取长度
	 */
	public function num_identify($num, $tbl, $default = 1) {
		$num = ( string ) $num;
		$len = \strlen ( $num );
		if ($len >= $default)
			$str = \substr ( $num, $len - $default, $default );
		else
			$str = \str_pad ( $num, $default, '0', \STR_PAD_LEFT );
		return $tbl . '_' . $str;
	}
	
	/**
	 * 求余数的方式获取分表-分库方法
	 * 1.
	 * 求余方式余数比较少，适合小型的分表法
	 * 2. 可以自定义求余除数
	 * Dao中使用方法：$this->dao->db->fmod_identify($num, $tbl, $default = 7)
	 * 
	 * @param int $num
	 * @param string $tbl
	 * @param int $default
	 * @return
	 *
	 */
	public function fmod_identify($num, $tbl, $default = 7) {
		return $tbl . '_' . \fmod ( $num / $default );
	}
}