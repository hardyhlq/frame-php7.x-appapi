<?php
namespace ChelerApi\dao\db;

use ChelerApi\ChelerApi;

class DB extends CDBs {
    
    use TSqlMaker;
    
	/**
	 * 重写MYSQL中的QUERY，对SQL语句进行监控
	 * @param string $sql
	 */
	public function query($sql, $is_set_default = true) {
		$this->getHandle($sql);
		
		$conf = ChelerApi::getConfig();
		
		if($conf['debug']) {
		    $start = microtime();
		}
		
		$query = $this->db->query($sql);
		
		if($conf['debug']){
		    $end   =   microtime();
		    if(isset($conf['sqlcontrolarr'])) {
		        $k = \count( $conf['sqlcontrolarr'] );
		    } else {
		        $k = 0;
		    }
		    
		    $conf['sqlcontrolarr'][$k]['sql'] = $sql;
		    $cost = \substr(($end-$start),0,7);
		    
		    $conf['sqlcontrolarr'][$k]['queryTime'] = $cost;
		    $ar = $this->affected_rows();
		    $conf['sqlcontrolarr'][$k]['affectedRows'] = $ar;
		    
		    ChelerApi::setConfig('sqlcontrolarr', $conf['sqlcontrolarr']);
		}
		
		if ($this->db->error()) {
		    ChelerApi::throwError($this->db->error());
		}
		
		if ($is_set_default) {
		    $this->setDefaultHandle();
		}
		
		return $query;
	}
	
	/**
	 * 结果集中的行数
	 * DAO中使用方法：$this->dao->db->result($result, $num=1)
	 * @param $result 结果集
	 * @return array
	 */
	public function result($result, $num=1) {
		return $this->db->result($result, $num);
	}
	
	/**
	 * 从结果集中取得一行作为关联数组
	 * DAO中使用方法：$this->dao->db->fetch_assoc($result)
	 * @param $result 结果集
	 * @return array
	 */
	public function fetch_assoc($result) {
		return $this->db->fetch_assoc($result);
	}
	
	/**
	 * 从结果集中取得列信息并作为对象返回
	 * DAO中使用方法：$this->dao->db->fetch_fields($result)
	 * @param  $result 结果集
	 * @return array
	 */
	public function fetch_fields($result) {
		return $this->db->fetch_fields($result);
	}
	

	/**
	 * 结果集中的行数
	 * DAO中使用方法：$this->dao->db->num_rows($result)
	 * @param $result 结果集
	 * @return int
	 */
	public function num_rows($result) {
		return $this->db->num_rows($result);
	}
	
	/**
	 * 结果集中的字段数量
     * DAO中使用方法：$this->dao->db->num_fields($result)
	 * @param $result 结果集
	 * @return int
	 */
	public function num_fields($result) {
		return $this->db->num_fields($result);
	}
	
	/**
	 * 释放结果内存
	 * DAO中使用方法：$this->dao->db->free_result($result)
	 * @param obj $result 需要释放的对象
	 */
	public function free_result($result) {
		return $this->db->free_result($result);
	}
	
	/**
	 * 获取上一INSERT的ID值
     * DAO中使用方法：$this->dao->db->insert_id()
	 * @return Int
	 */
	public function insert_id() {
		return $this->db->insert_id();
	}
	
	/**
	 * 前一次操作影响的记录数
	 * DAO中使用方法：$this->dao->db->affected_rows()
	 * @return int
	 */
	public function affected_rows() {
		return $this->db->affected_rows();
	}
	
	/**
	 * 关闭连接
	 * DAO中使用方法：$this->dao->db->close()
	 * @return bool
	 */
	public function close() {
		return $this->db->close();
	}
	
	/**
	 * 错误信息
	 * DAO中使用方法：$this->dao->db->error()
	 * @return string
	 */
	public function error() {
		return $this->db->error();
	}
	
	/**
	 * 开始事务操作
 	 * DAO中使用方法：$this->dao->db->transaction_start()
	 */
	public function transaction_start() {
		$this->query('START TRANSACTION');
		return true;
	}
	
	/**
	 * 提交事务
 	 * DAO中使用方法：$this->dao->db->transaction_commit()
	 */
	public function transaction_commit() {
		$this->query('COMMIT');
		return true;
	}
	
	/**
	 * 回滚事务
	 * DAO中使用方法：$this->dao->db->transaction_rollback()
	 */
	public function transaction_rollback() {
		$this->query('ROLLBACK'); 
		return true;
	}
	
	
	/** 
	 * SQL操作-插入一条数据
	 * DAO中使用方法：$this->dao->db->insert($data, $table_name)
	 * @param array  $data array('key值'=>'值')
	 * @param string $table_name 表名
	 * @return id
	 */
	public function insert(array $data, $table_name) {
		if (empty($data)) {
		    return 0;
		}
		$data = $this->makeInsert($data);
		$sql = sprintf('INSERT INTO %s %s', $table_name, $data);
		
		$result = $this->query($sql, false);
		if (!$result) {
		    return 0;
		}
		$id = $this->insert_id();
		$this->setDefaultHandle();
		return $id;
	}

	/**
	 * SQL操作-如果不存在则出入，否则替换
	 * DAO中使用方法：$this->dao->db->replace($data, $table_name)
	 * @author lonphy 谨慎使用
	 * @param array $data array('k'=>'v')
	 * @param string $table_name 表名
	 * @return num
	 */
	public function replace(array $data, $table_name) {
		if (empty($data)) {
		    return 0;
		}
		$data = $this->makeInsert($data);
		$sql = sprintf('REPLACE INTO %s %s', $table_name, $data);
		$result = $this->query($sql, false);
		$this->setDefaultHandle(); //设置默认的link_id
		return $result;
	}
	
	/**
	 * SQL批量操作-如果不存在则出入，否则替换
	 * DAO中使用方法：$this->dao->db->replace_more($data, $table_name)
	 * @author lonphy 谨慎使用
	 * @param array $data array('k'=>'v')
	 * @param string $table_name 表名
	 * @return num
	 */
	public function replace_more(array $data, $table_name) {
		if ( empty($data[0])) {
		    return 0;
		}
		
		$field = $this->makeInserts($data);
		
		$sql = sprintf('REPLACE INTO %s %s', $table_name, $field);
		$result = $this->query($sql,false);
		$this->setDefaultHandle(); //设置默认的link_id
		return $result;
	}
	
	/**
	 * SQL操作-插入多条数据
	 * DAO中使用方法：$this->dao->db->insert_more($field, $data, $table_name)
	 * @param array $field 字段
	 * @param array $data  对应的值，array(array('test1'),array('test2'))
	 * @param string $table_name 表名
	 * @return id
	 */
	public function insert_more($field,array $data, $table_name) {
		if (empty($data)) {
		    return false;
		}
		$sql = $this->makeInserts($field,$data);
		$sql = sprintf('INSERT INTO %s %s', $table_name, $sql);
		$result = $this->query($sql);
		$this->setDefaultHandle(); //设置默认的link_id
		return $result;
	}
	
	/**
	 * SQL操作-根据主键id更新数据
	 * DAO中使用方法：$this->dao->db->update($id, $data, $table_name, $id_key = 'id')
	 * @param  int    $id 主键ID
	 * @param  array  $data 参数
	 * @param  string $table_name 表名
	 * @param  string $id_key 主键名
	 * @return bool
	 */
	public function update($id, array $data, $table_name, $id_key = 'id') {
		$id = (int) $id;
		if ($id < 1) {
		    return false;
		}
		$data = $this->makeUpdate($data);
		$where = $this->makeWhere([$id_key=>$id]);
		
		
		
		
		$sql = sprintf('UPDATE %s %s %s', $table_name, $data, $where);
		echo $sql;
		exit;
		
		$result = $this->query($sql, false);
		$rows = $this->affected_rows();
		$this->setDefaultHandle(); //设置默认的link_id
		return $rows;
	}
	

	
	/**
	 * SQL操作-根据字段更新数据
	 * DAO中使用方法：$this->dao->db->update_by_field($data, $field, $table_name)
	 * @param  array  $data 参数
	 * @param  array  $field 字段参数
	 * @param  string $table_name 表名
	 * @param  string $upGlue set字段附加，只支持+，-
	 * @param  string $fieldGlue where条件附加,如 in等
	 * @return bool
	 */
	public function update_by_field(array $data, array $field, $table_name) {
		if (empty($data) || empty($field)) {
		    return false;
		}
		$field = $this->makeWhere($field);
		$data = $this->makeUpdate($data);
		$sql = sprintf('UPDATE %s %s %s', $table_name, $data, $field);
		$this->query($sql, false);
		$rows = $this->affected_rows();
		$this->setDefaultHandle(); //设置默认的link_id
		return $rows;
	}
	
	/**
	 * SQL操作-删除数据
	 * DAO中使用方法：$this->dao->db->delete($ids, $table_name, $id_key = 'id')
	 * @param  int|array $ids 单个id或者多个id
	 * @param  string $table_name 表名
	 * @param  string $id_key 主键名
	 * @return bool
	 */
	public function delete($ids, $table_name, $id_key = 'id') {
		if (\is_array($ids)) {
			$ids = $this->makeIn($ids);
			$sql = \sprintf('DELETE FROM %s WHERE %s %s', $table_name, $id_key, $ids);
		} else {
			$where = $this->makeWhere([$id_key=>$ids]);
			$sql = \sprintf('DELETE FROM %s %s', $table_name, $where);
		}
		return $this->query($sql);
	}
	
	/**
	 * SQL操作-通过条件语句删除数据
	 * DAO中使用方法：$this->dao->db->delete_by_field($field, $table_name)
	 * @param  array  $field 条件数组
	 * @param  string $table_name 表名
	 * @return bool
	 */
	public function delete_by_field(array $field, $table_name) {
		if (empty($field)) {
		    return false;
		}
		$where = $this->makeWhere($field);
		$sql = \sprintf('DELETE FROM %s %s', $table_name, $where);
		return $this->query($sql);
	}
	
	/**
	 * SQL操作-获取单条信息
	 * DAO中使用方法：$this->dao->db->get_one($id, $table_name, $id_key = 'id')
	 * @param int    $id 主键ID
	 * @param string $table_name 表名
	 * @param string $id_key 主键名称，默认id
	 * @return array
	 */
	public function get_one($id, $table_name, $id_key = 'id') {
		$id = (int) $id;
		if ($id < 1) {
		    return []; 
		}
		$where = $this->makeWhere([$id_key=>$id]);
		$sql = \sprintf('SELECT * FROM %s %s LIMIT 1', $table_name, $where);
		$result = $this->query($sql, false);
		if (!$result) {
		    return false;
		}
		$r = $this->fetch_assoc($result);
		$this->setDefaultHandle(); //设置默认的link_id
		return $r;
	}
	
	/**
	 * SQL操作-通过条件语句获取一条信息
	 * DAO中使用方法：$this->dao->db->get_one_by_field($field, $table_name)
	 * @param  array  $field 条件数组 array('username' => 'username')
	 * @param  string $table_name 表名
	 * @return bool
	 */
	public function get_one_by_field(array $field, $table_name) {
		if (empty($field)) {
		    return [];
		}
		
		$where = $this->makeWhere($field);
		$sql = \sprintf('SELECT * FROM %s %s LIMIT 1', $table_name, $where);
		
		$result = $this->query($sql, false);
		if (!$result) {
		    return false;
		}
		$r = $this->fetch_assoc($result);
		$this->setDefaultHandle(); //设置默认的link_id
		return $r;
	}
	
	/**
	 * SQL操作-获取单条信息-sql语句方式
	 * DAO中使用方法：$this->dao->db->get_one_sql($sql)
	 * @param  string $sql 数据库语句
	 * @return array
	 */
	public function get_one_sql($sql) {
		$sql = \trim($sql . ' ' .$this->makeLimit(1));
		$result = $this->query($sql, false);
		if (!$result) {
		    return false;
		}
		$r = $this->fetch_assoc($result);
		$this->setDefaultHandle(); //设置默认的link_id
		return $r;
	}
	
	/**
	 * SQL操作-获取全部数据[2014.8.25之后开发作废次封装方法]
	 * DAO中使用方法：$this->dao->db->get_all()
	 * @param string $table_name 表名
	 * @param array  $field 条件语句
	 * @param int    $num 分页参数
	 * @param int    $offest 获取总条数
	 * @param int    $key_id KEY值
	 * @param string $sort 排序键
	 * @return array array(数组数据，统计数)
	 * @deprecated
	 */
	public function get_all($table_name, $num = 20, $offest = 0, array $field = [], $id_key = 'id', $sort = 'DESC') {
		$where = $this->makeWhere($field);
		$limit = $this->makeLimit($offest, $num);
		$sql = sprintf('SELECT * FROM %s %s ORDER BY %s %s %s', $table_name, $where, $id_key, $sort, $limit);
		$result = $this->query($sql, false);
		if (!$result) {
		    return false;
		}
		$temp = [];
		while ($row = $this->fetch_assoc($result)) {
			$temp[] = $row;
		}
		$count = $this->get_count($table_name, $field);
		$this->setDefaultHandle(); //设置默认的link_id
		return [$temp, $count];
	}
	
	/**
	 * SQL操作-通过条件语句获取所有信息
	 * DAO中使用方法：$this->dao->db->get_all_by_field($field, $table_name)
	 * @param  array  $field 条件数组 array('username' => 'username')
	 * @param  string $table_name 表名
	 * @author baiyuxiong
	 * @return bool
	 */
	public function get_all_by_field(array $field, $table_name) {
		if ( empty($field) ) {
		    return [];
		}
		$where = $this->makeWhere($field);
		$sql = \sprintf('SELECT * FROM %s %s', $table_name, $where);
		$result = $this->query($sql, false);
		if (!$result) {
		    return false;
		}
	
		$temp = array();
		while ($row = $this->fetch_assoc($result)) {
			$temp[] = $row;
		}
		$this->setDefaultHandle(); //设置默认的link_id
		return $temp;
	}
	
	/**
	 * SQL操作-获取全部数据[不统计总数，只显示下一页，提高翻页性能]
	 * ==============
	 * 查询时 多查一条数据，用来判断是否有下一页内容
	 * 通过 array_pop 删除最后一条数据
	 * ==============
	 * DAO中使用方法：$this->dao->db->get_all_next()
	 * @param string $table_name 表名
	 * @param array  $field 条件语句
	 * @param int    $num 分页参数
	 * @param int    $offest 获取总条数
	 * @param int    $key_id KEY值
	 * @param string $sort 排序键
	 * @return array array(数组数据，是否可以翻页)
	 */
	public function get_all_next($table_name, $num = 20, $offest = 0, array $field = [], $id_key = 'id', $sort = 'DESC') {
		$where = $this->makeWhere($field);
		$newnum = $num + 1;
		$limit = $this->makeLimit($offest, $newnum);
		$sql = sprintf('SELECT * FROM %s %s ORDER BY %s %s %s', $table_name, $where, $id_key, $sort, $limit);
		$result = $this->query($sql, false);
		if (!$result) {
		    return false;
		}
		$temp = [];
		while ($row = $this->fetch_assoc($result)) {
			$temp[] = $row;
		}
		$this->free_result($result);
		$this->setDefaultHandle(); //设置默认的link_id
		
		//删除多余的一条数据 -- begin
		if(count($temp) > $num ){
			$nextPage = 1;
			array_pop($temp);
		}else{
			$nextPage = -1;
		}
		//删除多余的一条数据 -- end
		return [$temp,$nextPage];
	}
	/**
	 * SQL操作-获取所有数据
	 * DAO中使用方法：$this->dao->db->get_all_sql($sql)
	 * @param string $sql SQL语句
	 * @return array
	 */
	public function get_all_sql($sql) {
		$sql = \trim($sql);
		$result = $this->query($sql, false);
		if (!$result) {
		    return false;
		}
		$temp = [];
		while ($row = $this->fetch_assoc($result)) {
			$temp[] = $row;
		}
		$this->setDefaultHandle(); //设置默认的link_id
		return $temp;
	}
	
	/**
	 * SQL操作-获取数据总数
	 * DAO中使用方法：$this->dao->db->get_count($table_name, $field = array())
	 * @param  string $table_name 表名
	 * @param  array  $field 条件语句
	 * @return int
	 */
	public function get_count($table_name, array $field = []) {
		$where = $this->makeWhere($field);
		$sql = \sprintf('SELECT COUNT(*) as count FROM %s %s LIMIT 1', $table_name, $where);
		$result = $this->query($sql, false);
		$result =  $this->fetch_assoc($result);
		$this->setDefaultHandle(); //设置默认的link_id
		return $result['count'];
	}	
}
