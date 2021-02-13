<?php

use \KuaKee\Widget\Base as WidgetBase;
use \KuaKee\Widget\Manager as WidgetManager;

class HelloWorld extends WidgetBase
{
    public function __construct() 
    {
        $this->registered = true;
    }

    public function main()
    {
         $this->registerHook('run', '__run');

         $this->registerHook('test', '__runTest');
    }

    public function __run() {

        $GLOBALS['page'] .= 'run widget 1';

    }

    public function __runTest() {
        
        $GLOBALS['page'] .= 'run widget 2';
        
    }
}