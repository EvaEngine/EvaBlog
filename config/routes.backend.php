<?php

return array(
    '/admin/category' => array(
        'module' => 'EvaBlog',
        'controller' => 'Admin\Category',
    ),
    '/admin/category/:action(/(\d+))*' => array(
        'module' => 'EvaBlog',
        'controller' => 'Admin\Category',
        'action' => 1,
        'id' => 3,
    ),
    '/admin/post' => array(
        'module' => 'EvaBlog',
        'controller' => 'Admin\Post',
    ),
    '/admin/post/:action(/(\d+))*' => array(
        'module' => 'EvaBlog',
        'controller' => 'Admin\Post',
        'action' => 1,
        'id' => 3,
    ),
    '/admin/post/process/:action(/(\d+))*' => array(
        'module' => 'EvaBlog',
        'controller' => 'Admin\Process',
        'action' => 1,
        'id' => 3,
    ),
    '/admin/topic' => array(
        'module' => 'EvaBlog',
        'controller' => 'Admin\Topic',
    ),
    '/admin/topic/:action(/(\d+))*' => array(
        'module' => 'EvaBlog',
        'controller' => 'Admin\Topic',
        'action' => 1,
        'id' => 3,
    ),
);
