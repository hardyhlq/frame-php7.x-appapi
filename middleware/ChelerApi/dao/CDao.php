<?php
namespace ChelerApi\dao;

use ChelerApi\dao\db\DB;
use ChelerApi\ChelerApi;

/**
 * 数据层基类
 * @author lonphy
 */

class CDao {
    /**
     * @var DB
     */
    public $db = NULL;
    
    /**
     * 运行数据库
     * 1. 初始化DB类  DAO中调用方法    $this->dao->db
     * @return DB
     */
    public function run_db() {
        if ($this->db === NULL) {
            $this->db = ChelerApi::loadclass('ChelerApi\dao\db\DB');
            $this->db->db('default');
        }
        return $this->db;
    }
}