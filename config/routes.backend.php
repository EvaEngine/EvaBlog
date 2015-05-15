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
    '/admin/category/process/:action(/(\d+))*' => array(
        'module' => 'EvaBlog',
        'controller' => 'Admin\CategoryProcess',
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
    'savePublished' => array(
        'pattern' => '/admin/post/save/published',
        'paths' => array(
            'module' => 'EvaBlog',
            'controller' => 'Admin\Post',
            'action' => 'savePublished'
        ),
        'httpMethods' => 'POST'
    ),
    'saveDraft' => array(
        'pattern' => '/admin/post/save/draft',
        'paths' => array(
            'module' => 'EvaBlog',
            'controller' => 'Admin\Post',
            'action' => 'saveDraft'
        ),
        'httpMethods' => 'POST'
    ),
    '/admin/post/process/:action(/(\d+))*' => array(
        'module' => 'EvaBlog',
        'controller' => 'Admin\Process',
        'action' => 1,
        'id' => 3,
    ),

    'pushNews' => array(
        'pattern' => '/admin/post/push/node/(\d+)',
        'paths' => array(
            'module' => 'EvaBlog',
            'controller' => 'Admin\Post',
            'action' => 'pushPost',
            'id' => 1
        ),
        'httpMethods' => 'GET'
    ),
    'pushEmptyNews' => array(
        'pattern' => '/admin/post/push/node',
        'paths' => array(
            'module' => 'EvaBlog',
            'controller' => 'Admin\Post',
            'action' => 'pushEmptyPost',
            'id' => 1
        ),
        'httpMethods' => 'GET'
    ),

);
