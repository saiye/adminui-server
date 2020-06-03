<?php

return array(
    'main' => array('name' => '系统设置','checked'=>false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            'sys' => array('name' => '权限设置', 'checked'=>false,'display' => true, 'act' => 'login', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'role-list' => array('name' => '角色列表','checked'=>false, 'display' => true, 'url' => 'main/sys/role-list', 'act' => 'default'),
                    'edit-role' => array('name' => '编辑角色','checked'=>false, 'display' => false, 'url' => 'main/sys/edit-role', 'act' => 'default'),
                    'add-role' => array('name' => '添加角色', 'checked'=>false,'display' => true, 'url' => 'main/sys/add-role', 'act' => 'default'),
                    'del-role' => array('name' => '删除角色', 'checked'=>false,'display' => false, 'url' => 'main/sys/del-role', 'act' => 'default'),
                    'user-list' => array('name' => '用户列表','checked'=>false, 'display' => true, 'url' => 'main/sys/user-list', 'act' => 'default'),
                    'edit-user' => array('name' => '编辑用户', 'checked'=>false,'display' => false, 'url' => 'main/sys/edit-user', 'act' => 'default'),
                    'add-user' => array('name' => '添加用户','checked'=>false, 'display' => true, 'url' => 'main/sys/add-user', 'act' => 'default'),
                    'lock-user' => array('name' => '锁定用户', 'checked'=>false,'display' => false, 'url' => 'main/sys/lock-user', 'act' => 'default'),
                ),
            ),
            'log' => array('name' => '日志管理','checked'=>false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'error' => array('name' => '程序log列表', 'checked'=>false,'display' => true, 'url' => 'main/log/error', 'act' => 'default'),
                    'log' => array('name' => '错误日志详情','checked'=>false, 'display' => false, 'url' => 'main/log/log', 'act' => 'default'),
                    'action-log' => array('name' => '后台访问log','checked'=>false, 'display' =>true, 'url' => 'main/log/action-log', 'act' => 'default'),
                    'log-log' => array('name' => '用户登录log','checked'=>false,'display' =>true, 'url' => 'main/log/login-log', 'act' => 'default'),
                ),
            ),
            'setting' => array('name' => '安全设置','checked'=>false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'list' => array('name' => '过滤列表', 'checked'=>false,'display' => true, 'url' => 'main/setting/list', 'act' => 'default'),
                    'add' => array('name' => '添加过滤','checked'=>false, 'display' => true, 'url' => 'main/setting/add', 'act' => 'default'),
                    'edit' => array('name' => '修改过滤', 'checked'=>false,'display' => false, 'url' => 'main/setting/edit', 'act' => 'default'),
                ),
            ),
            'info' => array('name' => '系统状态','checked'=>false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'phpinfo' => array('name' => 'phpinfo', 'checked'=>false,'display' => true, 'url' => 'main/info/phpinfo', 'act' => 'default'),
                    'probe' => array('name' => 'php探针','checked'=>false, 'display' => true, 'url' => 'main/info/probe', 'act' => 'default'),
                    'clear-cache' => array('name' => '清理缓存', 'checked'=>false,'display' => true, 'url' => 'main/info/clear-cache', 'act' => 'default'),
                ),
            ),
        ),
    ),
    'test' => array('name' => '测试系统', 'checked'=>false,'display' => true, 'act' => 'login', 'class' => 'fa fa-share',
        'child' => array(
            'image' => array('name' => '图片处理', 'checked'=>false,'display' => true, 'act' => 'login', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'list' => array('name' => '图片列表','checked'=>false, 'display' => true, 'url' => 'test/image/list', 'act' => 'default'),
                ),
            ),
        ),
    ),
);
