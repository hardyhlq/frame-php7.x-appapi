<?php

namespace controller\v200;

use \controller\helper\BaseController;

class indexController extends BaseController {
	const key = 'lw-00001';
	
	public function run() {
		// $this->Redis()->geoadd($key, 'point1', [131.50, 31.39]);
		
		$result = $this->Redis ()->georadius ( self::key, [ 131.54,31.50 ], 100000, 10, REDIS_WITHCOORD | REDIS_WITHDIST |REDIS_ASC);
		
		$this->apiSuccess ( 200, '查询(131.54,31.50)方圆100,000m内的元素', $result );
	}
	
	public function t() {
		// $this->Redis()->geoadd($key, 'point1', [131.50, 31.39]);

		$result = $this->Redis ()->georadiusbymember( self::key, 'point1', 100000, 10, REDIS_WITHCOORD | REDIS_WITHDIST );
		$this->apiSuccess ( 200, '查询point1方圆100,000m内的元素', $result );
	}
}