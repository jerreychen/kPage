<?php

namespace app\home\controller;

use \KuaKee\Config;
use \KuaKee\Controller;
use \KuaKee\Widget\Manager as WidgetManager;

class Base extends Controller
{
    public function __init__()
    {
        //TODO: load widgets
        //Run widgets
        $GLOBALS['page'] = '';

        //Config::set('widget.enable', true);
        //Config::set('widget.list', ['HelloWorld', 'test']);

        WidgetManager::getInstance()->triggerByHook('test');

    }
}