<?php
namespace ChelerApi\runtime;
use \ChelerApi\runtime\CRpcException;
use \ChelerApi\ChelerApi;
/**
 * RPC服务层代理
 *
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */

class CRpcService {
    private static $timeout = 30;  // 请求超时
    private static $address = 'http://127.0.0.1:8080/';
    public $entry = '';  // 请求地址
    
    /**
     * 初始化配置
     */
    public static function init() {
        $conf = ChelerApi::getConfig('rpc');
        self::$address = rtrim($conf['address'], '/').'/' ?: self::$address;
        self::$timeout = $conf['timeout'] ?: self::$timeout; 
    }
    
    /**
     * 代理构造
     * @param string $servicename
     */
    public function __construct($servicename, $path='b'){
        $this->entry = self::$address. 'index.php?m=' .$path.'.' . $servicename;
    }
    
    public function __call($name, $args) {
        $url = $this->entry.'.'.$name;
        return self::exec($url, ['args'=>base64_encode(serialize($args))]);
    }
    
    /**
     * 执行CURL获取远程数据
     */
    private static function exec($url, $data) {
        if (!$url) return false;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_NOSIGNAL, 1);    // 解决28号错误 DNS解析问题
        curl_setopt($curl, CURLOPT_POST, 1 );			// POST提交
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data );
        curl_setopt($curl, CURLOPT_TIMEOUT, self::$timeout); //设置cURL允许执行的最长秒数。
    
        $content = curl_exec($curl);
        $errno = curl_errno($curl);
        $errmsg = curl_error($curl);
        curl_close($curl);
        if ($errno > 0){
            $msg = sprintf('curl(%d) %s', $errno, $errmsg);
            throw new CRpcException($msg, 500, $url);
        }
        $result = json_decode($content, 1);
        if ($result['code'] != 200) {
            throw new CRpcException($result['msg'], 500, $url, $result['code']);
        }
        return $result['data'];
    }
}