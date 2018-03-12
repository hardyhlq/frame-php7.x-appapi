<?php
namespace ChelerApi\library;  
/*********************************************************************************
 * InitPHP 3.8.2 国产PHP开发框架   扩展类库-日期处理
 *-------------------------------------------------------------------------------
 * 版权所有: CopyRight By initphp.com
 * 您可以自由使用该源码，但是在使用过程中，请保留作者信息。尊重他人劳动成果就是尊重自己
 *-------------------------------------------------------------------------------
 * Author:zhuli Dtime:2014-11-25 
***********************************************************************************/
class Ldate {  
    /**
     * 获取指定时间的周一
     * @param int $point 指定的时间戳
     * @return int 周一的时间戳
     * @author lonphy
     * @version 1.0 2015-4-1
     */
    public function getFirstDateOfWeek($point=0, $output_str=false) {
        $point = $point?: time();
        $point = strtotime(date('Y-m-d', $point));
        $index_of_week = date('w', $point);
        $point = $point-(($index_of_week==0?7:$index_of_week-1)*86400);
        return $output_str?date('Y-m-d', $point):$point;
    }
    
    /**
     * 计算指定时间周末
     * @param number $point
     * @param string $output_str
     * @author lonphy
     * @version 1.0 2015-4-1
     */
    public function getLastDateOfWeek($point=0, $output_str=false) {
        $point = $this->getFirstDateOfWeek($point);
        $point = $point+518400;	// 86400*6
        return $output_str?date('Y-m-d', $point):$point;
    }
    
    /**
     * 获取指定时间的上周一
     * @param int $point 指定的时间戳
     * @return int 周一的时间戳
     * @author lonphy
     * @version 1.0 2015-4-1
     */
    public function getFirstDateOfLastWeek($point=0, $output_str=false) {
        $point = $this->getFirstDateOfWeek($point);
        $point = $point - 604800;	// 86400*7
        return $output_str?date('Y-m-d', $point):$point;
    }
    
    /**
     * 获取指定时间的上周末
     * @param int $point 指定的时间戳
     * @return int 周一的时间戳
     * @author lonphy
     * @version 1.0 2015-4-1
     */
    public function getLastDateOfLastWeek($point=0, $output_str=false) {
        $point = $this->getFirstDateOfWeek($point);
        $point = $point-86400;
        return $output_str?date('Y-m-d', $point):$point;
    }
    
    /**
     * 获取本月初时间戳
     *
     * @param number $point 指定的时间戳
     * @return number 本月第一天的时间戳
     * @author lonphy
     * @version 1.0 2015-4-1
     */
    public function getFirstDateOfMonth($point=0, $output_str=false) {
        $point = $point?: time();
        $point = strtotime(date('Y-m-01', $point));
        return $output_str?date('Y-m-d', $point):$point;
    }
    /**
     * 获取本月末时间戳
     *
     * @param number $point 指定的时间戳
     * @return number 本月第一天的时间戳
     * @author lonphy
     * @version 1.0 2015-4-1
     */
    public function getLastDateOfMonth($point=0, $output_str=false) {
        $point = $point?: time();
        $point = strtotime(date('Y-m-t', $point));
        return $output_str?date('Y-m-d', $point):$point;
    }
    
    /**
     * 获取上月初时间戳
     *
     * @param number $point 指定的时间戳
     * @return number 本月第一天的时间戳
     * @author lonphy
     * @version 1.0 2015-4-1
     */
    public function getFirstDateOfLastMonth($point=0, $output_str=false) {
        // 获取本月初
        $point = $this->getFirstDateOfMonth($point);
        $point = strtotime(date('Y-m-01', $point-1));
        return $output_str?date('Y-m-d', $point):$point;
    }
    
    /**
     * 获取上月底时间戳
     *
     * @param number $point 指定的时间戳
     * @return number 上月底时间戳
     * @author lonphy
     * @version 1.0 2015-4-1
     */
    public function getLastDateOfLastMonth($point=0, $output_str=false) {
        // 获取本月初
        $point = $this->getFirstDateOfMonth($point);
        $point = $point-86400;
        return $output_str?date('Y-m-d', $point):$point;
    }
    
    /**
     * 生成指定日期范围的序列
     * @param string $start 序列开始 开始必须小于结束 格式 xxxx-xx-xx
     * @param string $end 序列结束 格式 xxxx-xx-xx
     * @author lonphy
     * @version 1.0 2015-4-1
     *
     */
    public function generateDateRange($start, $end) {
        $start = strtotime($start);
        $end = strtotime($end);
        if (!$start || !$end || $start>= $end) return false;
        $ret = [];
        while ($start<=$end) {
            $ret[] = date('Y-m-d', $start);
            $start += 86400;
        }
        return $ret;
    }
	
	private $year, $month, $day;  //定义年 月 日
	
	/**
	 * 计算2个时间戳之间相差的年月日[跨年月份貌似有点问题]
	 * @param int $start 开始时间戳
	 * @param int $end 结束时间戳
	 * @return array(year, month, date)
	 * @author lonphy
	 * @version 1.0 2015-3-23
	 */
	public function diffDate($start, $end) {
	    list($year1, $month1, $date1) = explode('-', date('Y-m-d', $start) );
	    list($year2, $month2, $date2) = explode('-', date('Y-m-d', $end) );
	    $y = $m = $d = $_m = 0;
	    $math = ($year2 - $year1) * 12 + $month2 - $month1;
	    $y = round($math / 12);
	    $m = intval($math % 12);
	    $d = (mktime(0, 0, 0, $month2, $date2, $year2) - mktime(0, 0, 0, $month2, $date1, $year2)) / 86400;
	    if ($d < 0) {
	        $m -= 1;
	        $d += date('j', mktime(0, 0, 0, $month2, 0, $year2));
	    }
	    $m < 0 && $y -= 1;
	    return array($y, $m, $d);
	}
	/**
	 * 两个日期相差月份
	 * @param int $start 开始时间戳
	 * @param int $end 结束时间戳
	 * @author lxm
	 */
	public function diffMonth($start, $end){
	    if(empty($start) || empty($end)) return 0;
	    $Y1 = date('y',$start);
	    $Y2 = date('y',$end);
	    $M1 = date('n',$start);
	    $M2 = date('n',$end);
	    return 12 - $M1 + ($Y2 - $Y1 - 1) * 12 + $M2; //计算月份差
	}
	/**
	 * 两个日期相差天数
	 * @param int $start 开始时间戳
	 * @param int $end 结束时间戳
	 * @author lxm
	 */
	function diffDays($start, $end){
	    if(empty($start) || empty($end)) return 0;
	    if($start > $end) return 0;
	    $a_dt=getdate($start);
	    $b_dt=getdate($end);
	    $a_new=mktime(12,0,0,$a_dt['mon'],$a_dt['mday'],$a_dt['year']);
	    $b_new=mktime(12,0,0,$b_dt['mon'],$b_dt['mday'],$b_dt['year']);
	    return round(abs($a_new-$b_new)/86400);
	}
	
	/**
	 *	日期-设置日期
	 * 	@param string   $date   日期格式2010-10-10
	 *  @return
	 */
	public function set_date($date = '') { 
		if ($date !== '') {
			list($year, $month, $day) = explode('-', $date);
			$this->set_year($year);
			$this->set_month($month);
			$this->set_day($day); 
		} else {
			$this->set_year(date('Y'));
			$this->set_month(date('m'));
			$this->set_day(date('d'));
		}
	} 
	
	/**
	 *	日期-增加天数
	 * 	@param  int  $day_num  多少天
	 *  @return int
	 */
	public function add_day($day_num = 1) {
		$day_num = (int) $day_num;
		$day_num = $day_num * 86400;
		$time = $this->get_time() + $day_num;
		$this->set_year(date('Y', $time));
		$this->set_month(date('m', $time));
		$this->set_day(date('d', $time));
		return $this->get_date();
	}

	/**
	 *	日期-获取当月最后一天
	 *  @return int
	 */
	public function get_lastday() {
		if($this->month==2) {
			$lastday = $this->is_leapyear($this->year) ? 29 : 28;
		} elseif($this->month==4 || $this->month==6 || $this->month==9 || $this->month==11) {
			$lastday = 30;
		} else {
			$lastday = 31;
		}
		return $lastday;
	}
	
	/**
	 *	日期-获取星期几
	 *  @return int
	 */
	public function get_week() {
		return date('w', $this->get_time());
	}
	
	/**
	 *	日期-是否是闰年
	 *  @return int
	 */
	public function is_leapyear($year) {
		return date('L', $year);
	}
	
	/**
	 *	日期-获取当前日期
	 *  @return string 返回：2010-10-10
	 */
	public function get_date() {
		return $this->year.'-'.$this->month.'-'.$this->day;
	}
	
	/**
	 *	日期-获取当前日期-不包含年-一般用户获取生日
	 *  @return string 返回：10-10
	 */
	public function get_birthday() {
		return $this->month.'-'.$this->day;
	}

	/**
	 *	日期-返回时间戳
	 *  @return int
	 */
	public function get_time() {
		return strtotime($this->get_date().' 23:59:59');
	}
	
	/**
	 *	日期-计算2个日期的差值
	 *  @return int
	 */
	public function get_difference($date, $new_date) {
		$date = strtotime($date);
		$new_date = strtotime($new_date);
		return abs(ceil(($date - $new_date)/86400));
	}
	
	/**
	 * 获取星期几
	 * @param int $week 处国人的星期，是一个数值，默认为null则使用当前时间
	 * @return string
	 */
	public static function getChinaWeek($week = null) {
		$week = $week ?: (int) date('w', time());
		return ['星期天', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'][$week];
	}
	
	/**
	 *	日期-设置年
	 * 	@param string  $year   年
	 *  @return
	 */
	private function set_year($year) {
		$year = (int) $year;
		$this->year = ($year <= 2100 && $year >= 1970) ? $year : date('Y');
	}
	
	/**
	 *	日期-设置月
	 * 	@param string  month  月
	 *  @return
	 */
	private function set_month($month) {
		$month = ltrim((int) $month, '0');
		$this->month = ($month < 13 && $month > 0) ? $month : date('m');
	}
	
	/**
	 *	日期-设置日
	 * 	@param string  day  天
	 *  @return
	 */
	private function set_day($day) {
		$day = ltrim((int) $day, '0');
		$this->day = ($this->year && $this->month && checkdate($this->month, $day, $this->year)) ? $day : date('d');
	}
	
}
