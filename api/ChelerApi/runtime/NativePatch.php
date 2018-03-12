<?php
/**
 * 通用的方法
 * @author lonphy
 */

/**
 * 判断是否是关联数组
 * @param  mixed $value
 * @return boolean
 */
function is_assoc($value) {
    return array_keys($value) !== range(0, count($value) - 1);
}
/**
 * 加密/解密函数 用于cookie 等
 * hash_code(10000, 'ENCODE');//加密  hash_code($auth_hash);//解密
 * @param string $string 加密/解密字符
 * @param string $operation ENCODE=加密   DECODE=解密
 * @param string $key 加密KEY
 * @param int $expiry
 * @return string
 */
function hashCode($string, $operation = 'DECODE', $key = '', $expiry = 0){
    global $InitPHP_conf;
    $string = str_replace('x2013x', '+', $string);
    $ckey_length = 6;
    $key = md5($key != '' ? $key : $InitPHP_conf['security']['hashkey']);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace(array('=','+'), array('','x2013x'), base64_encode($result));
    }

}