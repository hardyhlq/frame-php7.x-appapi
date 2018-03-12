<?php
namespace modules\dao\test;

class TestDao extends \ChelerApi\Dao {
    
    public function Test() {
        $result = $this->init_db('default')->get_all_next('test');
        return $result;
    }
}