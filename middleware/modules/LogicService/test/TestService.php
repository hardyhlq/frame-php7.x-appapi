<?php

namespace modules\LogicService\test;

use ChelerApi\ChelerApi;

class TestService extends \ChelerApi\runtime\CLogicService {
	public function LogicTest() {
		$result = [
		 'a'=>	'',
				'b'=>0,
				'c'=>'0'|0
		];
// 		$result ['test'] = $this->_getTestService ()->BaseTest ();
// 		$result ['test1'] = $this->_getTest2Service ()->BaseTest ();
// 		$result ['test3'] = $this->_getTestService ()->BaseTest ();
// 		$result ['test4'] = $this->_getTest2Service ()->BaseTest ();
		return $result;
	}
	
	/**
	 *
	 * @return \modules\BaseService\test\TestService
	 */
	private function _getTestService() {
		return ChelerApi::getBaseService ( 'test\Test' );
	}
	
	/**
	 *
	 * @return \modules\BaseService\test1\Test2Service
	 */
	private function _getTest2Service() {
		return ChelerApi::getBaseService ( 'test1\Test2' );
	}
}