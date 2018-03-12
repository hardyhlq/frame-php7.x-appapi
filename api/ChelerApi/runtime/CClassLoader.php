<?php
namespace ChelerApi\runtime;
/**
 * 类加载器
 * 
 * @author lonphy(dev@lonphy.com)
 * @version 1.0
 */
class CClassLoader {
    /**
     * 命名空间对应的路径
     * @var array
     */
    private static $namespaces = [];
    
    /**
     * 自定义的加载路径
     * @var type
    */
    private static $definitions = [];

    /**
     * 注册命名空间.
     *
     * @param string       $namespace 命名空间名称
     * @param array|string $paths     命名空间路径
     */
    public static function registerNamespace($namespace, $paths) {
        self::$namespaces[ $namespace ] = (array) $paths;
    }
    
    /**
     * 注册自定义命名空间
     *
     * @param string       $namespace 命名空间名称
     * @param array|string $paths     命名空间路径
     */
    public static function registerDefinition($namespace, $paths) {
        self::$definitions[$namespace] = (array) $paths;
    }
    
    /**
     * 注册本实例到autoloader
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not
     */
    public static function register($prepend = false) {
        spl_autoload_register([self::class, 'loadClass'], true, $prepend);
    }
    
    /**
     * 加载指定类或接口
     *
     * @param string $class 类名
     */
    public static function loadClass($class) {
        if (!!($file = self::findFile($class))) {
            require $file;
        }
    }
    
    /**
     * 查找类并返回文件路径
     * @param  string $class
     * @return string
     */
    public static function findFile($class) {
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }
        
        // 判断类是否带有命名空间
        if (false !== ($pos = strrpos($class, '\\')) ) {
            
            // 获取命名空间前缀
            $namespace = substr($class, 0, $pos);
            // 优先遍历内部命名空间
            foreach (self::$namespaces as $ns => $dirs) {
                if ( strpos($namespace, $ns) !== 0) {
                    continue;
                }
                
                $p = strpos($namespace, '\\');
                if ($p !== false) {
                    $namespace_path = substr($namespace, strpos($namespace, '\\')+1);
                }
                foreach ($dirs as $dir) {
                    $className = substr($class, $pos + 1);
                    $pathPart = '';
                    if (!empty($namespace_path)) {
                        $pathPart = str_replace('\\', DIRECTORY_SEPARATOR, $namespace_path) . DIRECTORY_SEPARATOR;
                    }
                    
                    $file = $dir.DIRECTORY_SEPARATOR . $pathPart. $className.'.php';
                    if (file_exists($file)) {
                        return $file;
                    }
                }
            }
            
            $m = explode('\\', $class);
            // Ignore wrong call
            if (count($m) <= 1) {
                return;
            }
            $class = array_pop($m);
            $namespace = implode('\\', $m);
            
            // 在自定义的命名空间中查找类
            foreach (self::$definitions as $ns => $dirs) {
                //Don't interfere with other autoloaders
                if (0 !== strpos($namespace, $ns)) {
                    continue;
                }

                foreach ($dirs as $dir) {
                    /**
                     * Available in service: Interface, Client, Processor, Rest
                     * And every service methods (_.+)
                     */
                    if(
                        0 === preg_match('#(.+)(if|client|processor|rest)$#i', $class, $n) and
                        0 === preg_match('#(.+)_[a-z0-9]+_(args|result)$#i', $class, $n)
                    )
                    {
                        $className = 'Types';
                    } else {
                        $className = $n[1];
                    }

                    $file = $dir.DIRECTORY_SEPARATOR .
                    str_replace('\\', DIRECTORY_SEPARATOR, $namespace) .
                    DIRECTORY_SEPARATOR .$className . '.php';

                    if (file_exists($file)) {
                        return $file;
                    }
                }
            }
        }
    }
}