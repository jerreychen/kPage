<?php

return [
    '/^\/([\w-]+)\/(\w*)$/' => [ 'module' => '\1', 'controller' => 'Index', 'action' => '\2' ]
];