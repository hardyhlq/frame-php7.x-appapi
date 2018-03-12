<?php
namespace modules\dao\test1;

class Test2Dao extends \ChelerApi\Dao {
    
    public function Test() {
        $result = $this->init_db('test1')->get_all_next('test2');
        return $result;
    }
}