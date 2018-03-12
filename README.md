Service开发文档
-

##目录结构
<pre>
	├─ChelerApi	 			框架核心
	│  ├─cache	 			缓存封装
	│  ├─dao	 			DB封装
	│  ├─library	 			框架扩展库
	│  ├─runtime	 			应用的运行时目录（可写，可定制）
	│  ├─util 				cooike、session等封装
	│  ├─ChelerApi.php			核心类
	├─conf					全局配置文件目录
	│  ├─conf.php				全局配置文件
	├─modules				应用模块目录
	│  ├─dao				模型目录
	│  ├─BaseService			基础服务层
	│  ├─helper 				项目配置文件目录
	│  ├─LogicService			逻辑服务层
	│  │  ├─car 				车辆模块
	│  │  ├─comm				公共模块
	│  │  ├─gps 				GPS功能模块
	│  │  ├─member				用户模块
	│  │  ├─message				车秘书模块
	│  │  ├─obd 				obd上报模块
	│  │  ├─prize 				奖品模块
	│  │  ├─renew 				盒子续费模块
	│  │  ├─...				...
	├─index.php				入口文件
</pre>


##命名规则
* Dao（数据层）层根据数据库分文件夹，根据表名驼峰创建文件
* BaseService（基础业务层）规则与Dao层一致,跟Dao一对一
* LogicService（逻辑业务层）按模块划分，统一Logic开头用于区分BaseService
* LogicService 按功能模块话创建，如用户相关的逻辑业务统一放在member目录下

##代码规范
* 使用Tab缩进，不要使用空格
* 逻辑层类文件统一大写开头，以Logic*****Service.php结构
* 赋值最好左右加空格，如$dealerid = $this->dealerInfo['id'];
* 使用框架实例化类｛ChelerApi::getBaseService('clw2\Member')｝放置在文件最底部
* 私有方法以_开头放置public方法的下方，如：private function _checkMobile(){}
* 区块业务结束可以回车空一行，方便阅读
* 每个方法需要写方法注释及签名，方法内写行内注释

##Dao开发
* 数据层所有类必须继承`\ChelerApi\dao\CDao`
* 数据层必须放入命名空间`\modules\dao\库名`
	
##基础服务层开发
* 基础服务层必须继承`ChelerApi\runtime\CBaseService`
* 基础服务层必须放入命名空间`\modules\BaseService\子包名`
* 基础服务层职责为:
* 控制对Dao层的访问过滤, 实现数据统一处理
    
##逻辑服务层开发
* 逻辑服务层必须继承`ChelerApi\runtime\CLogicService`
* 逻辑服务层必须放入命名空间`\modules\LogicService\子包名`
	*统一以Logic开头命名
	*组装多个BaseService与同等级LogicService之间复杂的业务逻辑

##调取规则
* 外层控制层可调取逻辑服务层（LogicService）+基础服务层（BaseService），不可直接调取Dao层
* 逻辑服务层可调取基础服务层（BaseService）+平等级逻辑服务层（LogicService），不可直接调取Dao层
* 基础服务层（BaseService）只可调用Dao层