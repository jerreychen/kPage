<?php
/**
 * Created by PhpStorm.
 * User: chenj
 * Date: 2018-12-11
 * Time: 8:11
 */

namespace KuaKee\Common;

final class Headers
{
    static private $_instance;

    private function __construct()
    {

    }

    static public function getInstance()
    {
        if(!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function add($name, $value)
    {
        header($name. ':' .$value);
    }

    public function remove($name)
    {
        header_remove($name);
    }

    public function get($name)
    {
        $name .= ':';
        $value = '';
        foreach (headers_list() as $header) {
            if(stripos($header, $name) === 0) {
                $value = $header;
                break;
            }
        }

        if(empty($value)) {
            return false;
        }

        return explode(':', $value)[1];
    }
}