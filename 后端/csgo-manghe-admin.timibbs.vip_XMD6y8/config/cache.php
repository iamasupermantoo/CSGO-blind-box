<?php

//return [
//    // 驱动方式
//    'type'   => 'File',
//    // 缓存保存目录
//    'path'   => '',
//    // 缓存前缀
//    'prefix' => '',
//    // 缓存有效期 0表示永久缓存
//    'expire' => 0,
//];

return [
    // 使用复合缓存类型
    'type'  =>  'complex',
    // 全局缓存有效期（0为永久有效）
    'expire'=>  10,
    // 默认使用的缓存
    'default'   =>  [
        // 驱动方式
        'type'   => 'file',
        // 缓存保存目录
        'path'   => '../runtime/default'
    ],
    // 文件缓存
    'file'   =>  [
        // 驱动方式
        'type'   => 'file',
        // 设置不同的缓存保存目录
        'path'   => '../runtime/file'
    ],
    // redis缓存
    'redis'   =>  [
        // 驱动方式
        'type'   => 'redis',
        'expire'=>  10,
        // 服务器地址
        'host'       => '127.0.0.1',
        //    // 缓存前缀
        'prefix' => 'redi',
        'password' => 'WdfRICEfOn',
    ],
];