<?php
/**
 * +-----------------------------------------------------------------------
 * | KPage，是温州市夸克网络科技有限公司内部使用的一套简单的php CMS系统框架。
 * +-----------------------------------------------------------------------
 * | 我们的宗旨是：用最少的代码，做更多的事情！
 * +-----------------------------------------------------------------------
 * | 应用程序入口
 * +-----------------------------------------------------------------------
 * @author          Jerrey
 * @license         MIT (https://mit-license.org)
 * @copyright       Copyright © 2021 KuaKee Team. (https://www.kuakee.com)
 * @version         v3
 */

declare (strict_types = 1);

namespace KuaKee;

use \KuaKee\Common\HtmlHelper;

final class View
{
    private $_base_path;
    private $_view_name;
    private $_view_data;

    public $htmlHelper;

    public function __construct($module, $relativePath, $viewName)
    {
        $this->_view_data = [];

        $this->_base_path = App::$rootPath . '/';
        $this->_base_path .= (empty($module) ? '' : ($module . '/'));
        $this->_base_path .= Config::get('view_path', 'view') . '/';
        $this->_base_path .= $relativePath;

        $this->_view_name = $viewName;
        $$viewName = $viewName;

        $this->htmlHelper = HtmlHelper::getInstance();
    }

    public function __set($name, $value)
    {
        $this->_view_data[$name] = $value;
    }

    public function __get($name)
    {
        return array_key_exists($name, $this->_view_data) ? $this->_view_data[$name] : '';
    }

    public function __isset($name)
    {
        return isset($this->_view_data[$name]);
    }

    public function __unset($name)
    {
        unset($this->_view_data[$name]);
    }

    public function setViewName($viewName)
    {
        $this->_view_name = $viewName;
    }
    public function setViewData($data) 
    {
        $this->_view_data = $data;
    }

    public function get($key, $defaultValue = null)
    {
        return Request::getInstance()->get($key, $defaultValue);
    }

    public function uriStartWith($uri)
    {
        if($uri == '/') {
            return $uri == Request::getInstance()->uri();
        }
        return \stripos(Request::getInstance()->uri(), $uri) === 0;
    }

    private function getTplFilePath($basePath, $path) 
    {
        $newPath = dirname($basePath);

       // $newPath = dirname($base_path);
        foreach(explode('/', ltrim($path, '/')) as $d) {
            if($d === '..') {
                $newPath = dirname($newPath);
            }
            else {
                $newPath .= '/' . $d;
            }
        }

        return $newPath;
    }

    private function getTplContentRecursive($base_path, $tpl_path, $content)
    {
        $tpl_path = $this->getTplFilePath($base_path, $tpl_path);

        if(!is_file($tpl_path)) {
            throw new Exception('View 文件：'.$tpl_path.' 不存在！');
        }
        
        $rgx_tpl_content = '/\<template\s*?name\s*?=\s*?"(?<name>.*?)".*?\>(?<content>[\s\S]*?)\<\/template\>/';
        preg_match_all($rgx_tpl_content, $content, $matches);
        $temp = []; 
        for($i = 0; $i<count($matches[0]); $i ++) {
            $temp[$matches['name'][$i]] = $matches['content'][$i];
        }

        $rgx_tpl_name = '/\<!template\s*?name\s*?=\s*?"(?<name>.*?)".*?\/\>/';
        $content = preg_replace_callback($rgx_tpl_name, function($matches) use ($temp) {
            return $temp[$matches['name']] ?? '';
        }, file_get_contents($tpl_path));

        // 是否template模式
        $rgx_tpl_path = '/\<!template\s*?path\s*?=\s*?"(?<path>.*?)".*?\/\>/';
        if(preg_match($rgx_tpl_path, $content, $matches)) {
            if(stripos(ltrim($content),$matches[0]) == 0) {
                return $this->getTplContentRecursive($tpl_path, $matches['path'], $content );
            }
            else {
                throw new Exception('View 文件：' . $tpl_path . ' 格式错误，<!template path="" /> 必须放在第一行。');
            }
        }
        
        return $this->getTplContent($tpl_path, $content); 
    }

    private function getTplContent($base_path, $content)
    {
        $rgx_tpl_include = '/\<!include\s*?path\s*?=\s*?"(?<path>.*?)".*?\/\>/';

        return preg_replace_callback($rgx_tpl_include, function($matches) use ($base_path) {
            $path = $this->getTplFilePath($base_path, $matches['path']);
            if($path && is_file($path)) {
                return file_get_contents($path);
            }

            return '';
        }, $content);
    }

    public function render()
    {
        $tmplExt = Config::get('tmpl.ext', '.tpl');
        $realTmplPath = $this->_base_path . '/' . $this->_view_name . $tmplExt;
        // 判断模板文件是否存在
        if(!is_file($realTmplPath)) {
            throw new Exception('View 文件：' . $realTmplPath . ' 不存在！');
        }

        $tpmlCacheDir = App::$rootPath . '/' . Config::get('tmpl.dir');
        // 模板缓存的路径不存在，创建
        if(! is_dir($tpmlCacheDir)) {
            if(! mkdir($tpmlCacheDir)) {
                throw new Exception('无法创建目录：' . $tpmlCacheDir . '！');
            }
        }

        // 缓存后的模板路径
        $cachedTmplPath = $tpmlCacheDir . '/' . md5($realTmplPath) . $tmplExt;

        // 缓存后的模板不存在或者缓存中的模板文件最后修改时间在真实模板文件之前
        if(is_file($cachedTmplPath) && (filemtime($realTmplPath) <= filemtime($cachedTmplPath))) {
            include_once $cachedTmplPath;
            return;
        }

        // 如果文件存在、先删除
        if(is_file($cachedTmplPath)) {
            \unlink($cachedTmplPath);
        }

        $content = file_get_contents($realTmplPath);

        $tpl_content = '';

        $rgx_tpl_path = '/\<!template\s*?path\s*?=\s*?"(?<path>.*?)".*?\/\>/';
        // 是否template模式
        if(preg_match($rgx_tpl_path, $content, $matches)) {
            if(stripos(ltrim($content),$matches[0]) == 0) {
                // 递归读取模板文件
                $tpl_content = $this->getTplContentRecursive($realTmplPath, $matches['path'], $content );
            }
            else {
                throw new Exception('View 文件：' . $realTmplPath . ' 格式错误，<!template path="" /> 必须放在第一行。'); 
            }
        }
        else {
            // 读取模板文件
            $tpl_content = $this->getTplContent($realTmplPath, $content);
        }

        file_put_contents($cachedTmplPath, $tpl_content);

        // 给页面设置 Content-Type 类型为 text/html
        header("Content-Type:text/html; charset=utf-8");

        include_once $cachedTmplPath;
        exit(); // 程序到这里执行结束
    }
}