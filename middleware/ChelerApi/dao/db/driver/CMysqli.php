<?php

namespace ChelerApi\dao\db\driver;

use ChelerApi\ChelerApi;

class CMysqli implements IDriver {
	
	/**
	 *
	 * @var \resouceID
	 */
	public $linkid;
	
	/**
	 * MYSQLI连接器
	 *
	 * @param string $host sql服务器
	 * @param string $user 数据库用户名
	 * @param string $password 数据库登录密码
	 * @param string $database 数据库
	 * @param string $charset 编码
	 * @return obj
	 */
	public function init($host, $user, $password, $database, $charset = 'utf8') {
		$linkid = \mysqli_connect ( $host, $user, $password );
		if (! $linkid) {
			ChelerApi::throwError ( 'mysqli connect error' );
		}
		\mysqli_query ( $linkid, 'SET NAMES ' . $charset );
		if (! \mysqli_select_db ( $linkid, $database )) {
			ChelerApi::throwError ( 'database ' . $database . ' is not exist!' );
		}
		return $linkid;
	}
	
	/**
	 * SQL执行器
	 *
	 * @param string $sql SQL语句
	 * @return \mysqli_result
	 */
	public function query($sql) {
		return \mysqli_query ( $this->linkid, $sql );
	}
	
	/**
	 * 结果集中的行数
	 *
	 * @param \mysqli_result $result 结果集
	 * @deprecated
	 *
	 */
	public function result($result, $num = 1) {
	}
	
	/**
	 * 从结果集中取得一行作为关联数组
	 *
	 * @param \mysqli_result $result 结果集
	 * @return array
	 */
	public function fetch_assoc($result) {
		return \mysqli_fetch_assoc ( $result );
	}
	
	/**
	 * 从结果集中取得列信息并作为对象返回
	 *
	 * @param \mysqli_result $result 结果集
	 * @return array
	 */
	public function fetch_fields($result) {
		return \mysqli_fetch_fields ( $result );
	}
	
	/**
	 * 结果集中的行数
	 *
	 * @param $result 结果集
	 * @return int
	 */
	public function num_rows($result) {
		return \mysqli_num_rows ( $result );
	}
	
	/**
	 * 结果集中的字段数量
	 *
	 * @param \mysqli_result $result 结果集
	 * @return int
	 */
	public function num_fields($result) {
		return \mysqli_num_fields ( $result );
	}
	
	/**
	 * 释放结果内存
	 *
	 * @param \mysqli_result $result 需要释放的对象
	 */
	public function free_result($result) {
		return \mysqli_free_result ( $result );
	}
	
	/**
	 * 获取上一INSERT的ID值
	 *
	 * @return Int
	 */
	public function insert_id() {
		return \mysqli_insert_id ( $this->linkid );
	}
	
	/**
	 * 前一次操作影响的记录数
	 *
	 * @return int
	 */
	public function affected_rows() {
		return \mysqli_affected_rows ( $this->linkid );
	}
	
	/**
	 * 关闭连接
	 *
	 * @return bool
	 */
	public function close() {
		if ($this->linkid !== null) {
			\mysqli_close ( $this->linkid );
		}
		$this->linkid = null;
		return true;
	}
	
	/**
	 * 错误信息
	 *
	 * @return string
	 */
	public function error() {
		return \mysqli_error ( $this->linkid );
	}
}
