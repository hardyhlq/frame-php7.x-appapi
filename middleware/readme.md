ChelerApi开发文档
-

##目录结构
<pre>
	/modules							#项目目录
    /modules/dao					    #项目数据层
	/modules/BaseService/test			#基础服务层
	/modules/LogicService/test			#逻辑服务层
</pre>

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