ChelerApi开发文档
-

##项目配置
* RPC配置:  
<pre>
$_CONFIG_['rpc']['address'] = 'http://127.0.0.1:8081/';  // rpc服务器地址
$_CONFIG_['rpc']['timeout'] = 10;	// 超时
</pre>

* 项目版本配置
<pre>
// 每个版本单独v[\d]+
// 每次发布版本需要更新配置文件,示例如下：
	$_CONFIG_['apiVersion']['v100'] = [
	    // 控制器名 => [方法1, 方法2]
	    'index' => ['run', 'test']
	];
	
	$_CONFIG_['apiVersion']['v200'] = [
	    // 控制器名 => 方法名列表
	    'index' => ['run']
	];
</pre>

##目录结构
<pre>
	/app							#项目目录
    /app/controller					#项目控制器目录
	/app/controller/v001			#v0.0.1版本控制器目录
	/app/controller/v002			#v0.0.2版本控制器目录
	/app/library
	/app/library/localService		#本地服务层 顶级包名
	/app/library/localService/test1		# 次级包名
</pre>

##控制器开发
* 控制器必须继承`\ChelerApi\Controller`
* 控制器必须放入命名空间`\controller\版本`
* 调用远程Service:  
	`ChelerApi::getRPCService('目录.服务名', version)`
* 调用本地Service:  
	`ChelerApi::getLocalService('目录\服务名')`
	
##本地服务层开发
* 本地服务层必须继承`ChelerApi\runtime\CLocalService`
* 本地服务层必须放入命名空间`\localService\子包名`