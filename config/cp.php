<?php

return array(
    'main' => array('name' => '系统设置', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            'sys' => array('name' => '权限设置', 'checked' => false, 'display' => true, 'act' => 'login', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'role-list' => array('name' => '角色列表', 'checked' => false, 'display' => true, 'url' => 'main/sys/role-list', 'act' => 'default'),
                    'edit-role' => array('name' => '编辑角色', 'checked' => false, 'display' => false, 'url' => 'main/sys/edit-role', 'act' => 'default'),
                    'add-role' => array('name' => '添加角色', 'checked' => false, 'display' => true, 'url' => 'main/sys/add-role', 'act' => 'default'),
                    'del-role' => array('name' => '删除角色', 'checked' => false, 'display' => false, 'url' => 'main/sys/del-role', 'act' => 'default'),
                    'user-list' => array('name' => '用户列表', 'checked' => false, 'display' => true, 'url' => 'main/sys/user-list', 'act' => 'default'),
                    'edit-user' => array('name' => '编辑用户', 'checked' => false, 'display' => false, 'url' => 'main/sys/edit-user', 'act' => 'default'),
                    'add-user' => array('name' => '添加用户', 'checked' => false, 'display' => true, 'url' => 'main/sys/add-user', 'act' => 'default'),
                    'lock-user' => array('name' => '锁定用户', 'checked' => false, 'display' => false, 'url' => 'main/sys/lock-user', 'act' => 'default'),
                ),
            ),
            'log' => array('name' => '日志管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'error' => array('name' => '程序log列表', 'checked' => false, 'display' => true, 'url' => 'main/log/error', 'act' => 'default'),
                    'show' => array('name' => '日志详情', 'checked' => false, 'display' => false, 'url' => 'main/log/show', 'act' => 'default'),
                    'action-log-list' => array('name' => '后台访问log', 'checked' => false, 'display' => true, 'url' => 'main/log/action-log-list', 'act' => 'default'),
                ),
            ),
        ),
    ),
    'company' => array('name' => '商户管理', 'checked' => false, 'display' => true, 'act' => 'login', 'class' => 'fa fa-share',
        'child' => array(
            'index' => array('name' => '商户列表', 'checked' => false, 'display' => true, 'act' => 'login', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'companyList' => array('name' => '商户列表', 'checked' => false, 'display' => true, 'url' => 'company/index/companyList', 'act' => 'default'),
                    'addCompany' => array('name' => '添加商户', 'checked' => false, 'display' => true, 'url' => 'company/index/addCompany', 'act' => 'default'),
                    'checkCompany' => array('name' => '审核商户', 'checked' => false, 'display' => true, 'url' => 'company/index/checkCompany', 'act' => 'default'),
                    'lockCompany' => array('name' => '锁定商户', 'checked' => false, 'display' => true, 'url' => 'company/index/lockCompany', 'act' => 'default'),
                    'getState' => array('name' => '国家列表', 'checked' => false, 'display' => true, 'url' => 'company/index/getState', 'act' => 'login'),
                    'areaList' => array('name' => '省市区', 'checked' => false, 'display' => true, 'url' => 'company/index/areaList', 'act' => 'login')
                )
            )
        ),
    ),
    'store' => array('name' => '门店管理', 'checked' => false, 'display' => true, 'act' => 'login', 'class' => 'fa fa-share',
        'child' => array(
            'index' => array('name' => '门店列表', 'checked' => false, 'display' => true, 'act' => 'login', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'storeList' => array('name' => '门店列表', 'checked' => false, 'display' => true, 'url' => 'store/index/storeList', 'act' => 'default'),
                    'addStore' => array('name' => '添加门店', 'checked' => false, 'display' => true, 'url' => 'store/index/addStore', 'act' => 'default'),
                    'checkStore' => array('name' => '审核门店', 'checked' => false, 'display' => true, 'url' => 'store/index/checkStore', 'act' => 'default'),
                )
            )
        ),
    ),
    'game' => array('name' => '游戏管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            'index' => array('name' => '会员管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'userList' => array('name' => '会员列表', 'checked' => false, 'display' => true, 'url' => 'game/index/userList', 'act' => 'default'),
                    'addUser' => array('name' => '添加会员', 'checked' => false, 'display' => true, 'url' => 'game/index/addUser', 'act' => 'default'),
                    'editUser' => array('name' => '编辑会员', 'checked' => false, 'display' => true, 'url' => 'game/index/editUser', 'act' => 'default'),
                    'lockUser' => array('name' => '锁定会员', 'checked' => false, 'display' => true, 'url' => 'game/index/lockUser', 'act' => 'default'),
                ),
            ),
            'channel' => array('name' => '渠道管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'channelList' => array('name' => '会员列表', 'checked' => false, 'display' => true, 'url' => 'game/channel/channelList', 'act' => 'default'),
                    'addChannel' => array('name' => '添加渠道', 'checked' => false, 'display' => true, 'url' => 'game/channel/addChannel', 'act' => 'default'),
                    'editChannel' => array('name' => '编辑渠道', 'checked' => false, 'display' => true, 'url' => 'game/channel/editChannel', 'act' => 'default'),
                ),
            ),
            'board' => array('name' => '板子管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'boardList' => array('name' => '板子列表', 'checked' => false, 'display' => true, 'url' => 'game/board/boardList', 'act' => 'default'),
                    'addBoard' => array('name' => '添加板子', 'checked' => false, 'display' => true, 'url' => 'game/board/addBoard', 'act' => 'default'),
                    'editBoard' => array('name' => '编辑板子', 'checked' => false, 'display' => true, 'url' => 'game/board/editBoard', 'act' => 'default'),
                ),
            ),
        ),
    ),
    'order' => array('name' => '订单管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            'index' => array('name' => '订单管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'orderList' => array('name' => '订单列表', 'checked' => false, 'display' => true, 'url' => 'order/index/orderList', 'act' => 'default'),
                    'addOrder' => array('name' => '下订单', 'checked' => false, 'display' => true, 'url' => 'order/index/addOrder', 'act' => 'default'),
                ),
            ),
        ),
    ),
    'room' => array('name' => '房间管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            'index' => array('name' => '房间管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'roomList' => array('name' => '房间列表', 'checked' => false, 'display' => true, 'url' => 'room/index/roomList', 'act' => 'default'),
                    'addRoom' => array('name' => '添加房间', 'checked' => false, 'display' => true, 'url' => 'room/index/addRoom', 'act' => 'default'),
                    'editRoom' => array('name' => '编辑房间', 'checked' => false, 'display' => true, 'url' => 'room/index/editRoom', 'act' => 'default'),
                    'companyAndRoomList' => array('name' => '商户，房间关联菜单', 'checked' => false, 'display' => true, 'url' => 'room/index/companyAndRoomList', 'act' => 'default'),
                ),
            ),
            'billing' => array('name' => '计费设置', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'billingList' => array('name' => '计费列表', 'checked' => false, 'display' => true, 'url' => 'room/billing/billingList', 'act' => 'default'),
                    'addBilling' => array('name' => '添加计费模式', 'checked' => false, 'display' => true, 'url' => 'room/billing/addBilling', 'act' => 'default'),
                    'billingConfig' => array('name' => '计费选择', 'checked' => false, 'display' => true, 'url' => 'room/billing/billingConfig', 'act' => 'default'),
                ),
            ),
        ),
    ),


);
