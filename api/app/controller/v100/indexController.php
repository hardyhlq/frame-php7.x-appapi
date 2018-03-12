<?php
namespace controller\v100;
use \ChelerApi\ChelerApi;
use \ChelerApi\Controller;




class indexController extends Controller {
    public function run() {
        $this->apiSuccess(200, 'ok', 'asdfas');
    }
    
    public function test() {
       $this->apiSuccess(200, '消息来自第一版 test方法，OK');
    }
}