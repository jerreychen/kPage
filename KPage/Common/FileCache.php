<?php
/**
 * Created by PhpStorm.
 * User: chenj
 * Date: 2018-12-11
 * Time: 16:27
 */

namespace KuaKee\Common;

use KuaKee\App;
use KuaKee\Config;

final class FileCache
{
    static private $_instance;

    private $_cache_dir, $_cache_ext;

    private function __construct()
    {
        $this->_cache_dir = App::$rootPath . '/' . Config::get('cache.file.dir', 'cache');
        $this->_cache_ext = Config::get('cache.file.ext', '.cache'); 

        // 如果 cache 目录不存在，创建目录
        if(! is_dir($this->_cache_dir) ) {
            if(! mkdir($this->_cache_dir)) {
                throw new \Exception('无法创建目录：' . $this->_cache_dir . '！');
            }
        }
    }

    static public function getInstance()
    {
        if(!self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
 
    /**
     * 读取 cache 文件路径
     * @param String $name
     * @return Boolean/String
     */
    private function getCachedFile($name)
    {
        // 搜索文件的规则
        $fwc = $this->_cache_dir .'/'. $name .'_'. md5($name) .'.*'. $this->_cache_ext;
        $cacheFiles = glob($fwc, GLOB_NOSORT);

        if((false === $cacheFiles) || (count($cacheFiles) == 0)) {
            return false;
        }

        foreach ($cacheFiles as $file) {
            // 取 filename
            $fileName = str_replace($this->_cache_dir.'/', '', $file);

            if(strpos($fileName, $name.'_'.md5($name)) === 0) {

                $fileSeg = explode('.', $fileName);

                // 是否过期，过期删除
                if(intval($fileSeg[1]) < time()) {
                    unlink($file);
                    return true;
                }

                return $file;
            }
        }

        return false;
    }

    /**
     * @param String $name
     * @param Object $value
     * @param Integer $expires = 7200 
     */
    public function add($name, $value, $expires = 7200)
    {
        $cachedFile = $this->getCachedFile($name);

        // 如果找到相同文件名的缓存文件，先删除旧文件
        if(false !== $cachedFile) {
            unlink($cachedFile);
        }

        // 如果没有找到该cache文件，新建一个
        $cacheFile = $this->_cache_dir .'/'. $name .'_'. md5($name) .'.'. (time() + $expires) . $this->_cache_ext;
        file_put_contents($cacheFile, serialize($value), LOCK_EX);
    }

    public function get($name)
    {
        $cachedFile = $this->getCachedFile($name);
        if(false === $cachedFile) {
            return false;
        }
        $value = file_get_contents($cachedFile);
        return empty($value) ? false : unserialize($value);
    }

    public function remove($name)
    {
        $cachedFile = $this->getCachedFile($name);
        if(false === $cachedFile) {
            return false;
        }
        unlink($cachedFile);
        return true;
    }
}