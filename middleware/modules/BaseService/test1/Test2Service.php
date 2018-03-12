<?php
namespace modules\BaseService\test1;

use ChelerApi\ChelerApi;
use modules\dao\test1\Test2Dao;

class Test2Service extends \ChelerApi\runtime\CBaseService {
    
    public function BaseTest() {
        return $this->_getTest2Dao()->Test();
    }
    
    /**
     * @return Test2Dao
     */
    private function _getTest2Dao() {
        return ChelerApi::getDao('test1\Test2');
    }
}