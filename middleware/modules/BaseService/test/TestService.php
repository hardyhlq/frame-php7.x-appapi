<?php
namespace modules\BaseService\test;

use ChelerApi\ChelerApi;
use modules\dao\test\TestDao;

class TestService extends \ChelerApi\runtime\CBaseService {
    
    public function BaseTest() {
        return $this->_getTestDao()->Test();
    }
    
    /**
     * @return TestDao
     */
    private function _getTestDao() {
        return ChelerApi::getDao('test\Test');
    }
}