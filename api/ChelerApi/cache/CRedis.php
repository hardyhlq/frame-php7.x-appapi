<?php

/**
 * 缓存类 - Redis
 *
 * 保存单例加载的实例
 *
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
namespace ChelerApi\cache;

define ( 'REDIS_WITHCOORD', 1 << 0 );
define ( 'REDIS_WITHDIST', 1 << 1 );
define ( 'REDIS_ASC', 1 << 2 );
define ( 'REDIS_DESC', 1 << 3 );
class CRedis {
	const lua_georadius_count_script = 
'if redis.call("EXISTS", KEYS[1]) == 1 then
	local points = redis.call("GEORADIUS", KEYS[1], ARGV[1], ARGV[2], ARGV[3], "m")
	return table.maxn(points)
else
	return -1
end';
const lua_georadiusmember_count_script =
	'if redis.call("EXISTS", KEYS[1]) == 1 then
	local points = redis.call("GEORADIUSBYMEMBER", KEYS[1], ARGV[1], ARGV[2], "m")
	return table.maxn(points)
else
	return -1
end';	
	/**
	 *
	 * @var \Redis
	 */
	private $redis;
	private $lua_georadius_count_sha='';
	private $lua_georadiusmember_count_sha='';
	
	public function Client(): \Redis {
		return $this->redis;
	}
	public function geoadd(string $key, string $name, array $pos): int {
		return $this->redis->rawcommand ( 'geoadd', $key, $pos [0], $pos [1], $name );
	}
	
	/**
	 * 返回由$pos与$radius决定的圆形区域内所有元素列表
	 *
	 * @param string $key 全局key
	 * @param array $pos 坐标点， [log,lat]
	 * @param int $radius 半径， 单位m
	 * @param int $count 数量
	 * @param int $flag 其他可选标记
	 *        REDIS_WITHCOORD, REDIS_WITHDIST,REDIS_ASC, REDIS_DESC
	 * @return array
	 */
	public function georadius(string $key, array $pos, int $radius, int $count = 100, int $flag = 0): array {
		if ($flag & 3) {
			if ($flag & 4) {
				return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'withcoord', 'withdist', 'count', $count, 'asc' );
			} else if ($flag & 8) {
				return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'withcoord', 'withdist', 'count', $count, 'desc' );
			}
			return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'withcoord', 'withdist', 'count', $count );
		} else if ($flag & 1) {
			if ($flag & 4) {
				return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'withcoord', 'count', $count, 'asc' );
			} else if ($flag & 8) {
				return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'withcoord', 'count', $count, 'desc' );
			}
			return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'withcoord', 'count', $count );
		} else if ($flag & 2) {
			if ($flag & 4) {
				return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'withdist', 'count', $count, 'asc' );
			} else if ($flag & 8) {
				return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'withdist', 'count', $count, 'desc' );
			}
		}
		
		if ($flag & 4) {
			return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'count', $count, 'asc' );
		} else if ($flag & 8) {
			return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'count', $count, 'desc' );
		}
		
		return $this->redis->rawcommand ( 'georadius', $key, $pos [0], $pos [1], $radius, 'm', 'count', $count );
	}
	
	public function geoRadiusCount(string $key, array $pos, int $radius): int {
		return $this->redis->evalSha($this->lua_georadius_count_sha, [$key, $pos[0], $pos[1], $radius], 1);
	}
	
	public function geoRadiusByMemberCount(string $key, string $name, int $radius): int {
		return $this->redis->evalSha($this->lua_georadiusmember_count_sha, [$key, $name, $radius], 1);
	}
	
	/**
	 * 返回由$name的坐标与$radius决定的圆形区域内所有元素列表
	 *
	 * @param string $key 全局key
	 * @param string $name 元素名称
	 * @param int $radius 半径， 单位m
	 * @param int $count 数量
	 * @param int $flag 其他可选标记
	 *        REDIS_WITHCOORD, REDIS_WITHDIST,REDIS_ASC, REDIS_DESC
	 * @return array
	 */
	public function georadiusbymember(string $key, string $name, int $radius, int $count = 100, int $flag = 0): array {
		if ($flag & 3) {
			if ($flag & 4) {
				return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'withcoord', 'withdist', 'count', $count, 'asc' );
			} else if ($flag & 8) {
				return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'withcoord', 'withdist', 'count', $count, 'desc' );
			}
			return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'withcoord', 'withdist', 'count', $count );
		} else if ($flag & 1) {
			if ($flag & 4) {
				return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'withcoord', 'count', $count, 'asc' );
			} else if ($flag & 8) {
				return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'withcoord', 'count', $count, 'desc' );
			}
			return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'withcoord', 'count', $count );
		} else if ($flag & 2) {
			if ($flag & 4) {
				return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'withdist', 'count', $count, 'asc' );
			} else if ($flag & 8) {
				return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'withdist', 'count', $count, 'desc' );
			}
		}
		
		if ($flag & 4) {
			return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'count', $count, 'asc' );
		} else if ($flag & 8) {
			return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'count', $count, 'desc' );
		}
		
		return $this->redis->rawcommand ( 'georadiusbymember', $key, $name, $radius, 'm', 'count', $count );
	}
	
	/**
	 * redis - 初始化客户端
	 */
	public function initClient(array $config) {
		if (empty ( $config )) {
			exit ( 'redis config is empty!' );
		}
		
		$this->redis = new \Redis ();
		$this->redis->pconnect ( $config ['host'], $config ['port'] ) or die ( 'redis connect faild.' );
		
		if ($config ['pass']) {
			$this->redis->auth ( $config ['pass'] ) or die ( 'redis auth faild.' );
		}
		
		$this->lua_georadius_count_sha = $this->redis->script('load', self::lua_georadius_count_script);
		$this->lua_georadiusmember_count_sha = $this->redis->script('load', self::lua_georadiusmember_count_script);

		if (isset ( $config ['db'] )) {
			$this->redis->select ( $config ['db'] );
		} else {
			$this->redis->select ( 0 );
		}
	}
}
