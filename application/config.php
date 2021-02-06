<?php

return [
    'namespace'             => 'app',
    'controller_path'       => 'controller',
    'view_path'             => 'view',

    'default_module'        => 'home',
    'default_controller'    => 'Index',
    'default_action'        => 'index',

    'helper_file'           => '/helper.php',

    'widget'                => [
        'enable'    => true,
        'dir'       => '/widgets',
        'list'      => ['HelloWorld']
    ],

    'cookie_domain'         => '',

    'debug'                 => true,
    'exception_only'        => false,    // 页面只显示错误信息，如果true会清空缓冲区内容
    'exception_page'        => lib_root() . '/Template/error.tpl',  // 错误页面的模板文件，可以改成自己的

    // 路由
    'route' => [
        'enable'    => true,
        'query_key' => 's',
        'router'    => 'router.php'
        /* 如果 enable = false, 需要配置一下参数 */
        , 'query_controller'  => 'c'  // 默认值 'c'
        , 'query_action'      => 'a'  // 默认值 'a'
    ],

    // 模板
    'tmpl' =>  [
        'compress'  => false,   // cached 为true时候 compress 生效
        'dir'       => 'tmpl',   // cached 为true时候 dir 生效
        'ext'       => '.tpl'
    ],

    // 数据库
    'database' => [
        'type'      => 'sqlite',    // 可以 sqlite / mysql（注意名称全小写）
        'sqlite'    => [
            'file'      => '',
            'charset'   => 'utf-8'
        ],
        'mysql'     => [
            'host'      => '',
            'port'      => 3306,
            'uid'       => '',
            'pwd'       => '',
            'charset'   => 'utf-8'
        ]
    ],

    // 缓存
    'cache' => [
        'type'      => 'file',      // 可以 file / redis（注意名称全小写）
        'file'      => [
            'dir'       => 'cache',
            'ext'       => '.cache'
        ],
        'redis'     => [
            'host'      => '192.168.3.251',
            'port'      => 6379,
            'pwd'       => 'Apple@123'
        ]
    ]
];