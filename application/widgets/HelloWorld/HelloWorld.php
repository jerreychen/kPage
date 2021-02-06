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
         $this->addFilter('run', '__run');

         $this->addFilter('test', '__runTest');
    }

    public function __run() {

        $GLOBALS['page'] .= 'run widget 1';

    }

    public function __runTest() {
        
        $GLOBALS['page'] .= 'run widget 2';
        
    }
}