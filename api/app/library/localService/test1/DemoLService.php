<?php
namespace localService\test1;

use ChelerApi\runtime\CLocalService;


class DemoLService extends CLocalService {
    
    public function test() {
        return 'hello , DemoLService.test called.';
    }
}