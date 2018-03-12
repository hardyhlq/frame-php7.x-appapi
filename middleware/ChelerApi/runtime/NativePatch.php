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
 * 车乐APP输出单位精度统一控制函数
 * @type fuel:油耗 毫升 转换 ml -> 升
 *       db:驾驶行为单位转换, x.x
 *       speed:速度
 * @str 是否返回字符串，默认不是
 * @author lxm
 * @version 1.0 2014.4.2
 */
function clwConvert($value,$type,$str = true){
    if(empty($value)) return "0";

    $res = $value;
    //油耗单位转换 unit:L
    if('fuel' == $type)
    {
        //需求 http://pms.17car.com.cn/www/index.php?m=story&f=view&storyID=49
        $res =  round($value/1000,2);
    }
    //车速 / 平均车速 unit:km/h
    elseif('speed' == $type)
    {
        $res =  round($value,1);
    }
    //平均油耗 / 油耗  unit:ml
    elseif('avgfuel' == $type)
    {
        $res =  round($value,1);
    }
    //积分 unit:分
    elseif('point' == $type)
    {
        $res =  round($value);
    }
    //公里 unit:km
    elseif('miles' == $type)
    {
        $res =  round($value);
    }
    //时间 unit:min
    else if("time" == $type)
    {
        $res =  round($value);
    }
    //各种百分比，例如车技较上月提升多少、你战胜了全国多少的车主等等 X.X% 允许带小数点后1位
    else if('percent' == $type)
    {
        $res =  round($value,1);
    }
    //对照类数据，例如行车里程可以绕地球X.X圈了 x.x 允许带小数点后1位
    else if('compare' == $type)
    {
        $res =  round($value,1);
    }
    //各种货币类数据，例如XX元 X 取整，单位：元
    else if('money' == $type)
    {
        $res =  round($value);
    }
    //次数
    else if('int' == $type){
        $res = round($value);
    }
    //X.X 其他上述未涉及到的数据统一允许带小数点后1位
    else
    {
        $res =  round($value,1);
    }

    $res = (String)$res;

    return $res;
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
