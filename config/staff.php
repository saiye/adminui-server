<?php

return array(
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
    'tool' => array('name' => '全局功能管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            'image' => array('name' => '图片上传', 'checked' => false, 'display' => false, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'upload' => array('name' => '上传图片', 'checked' => false, 'display' => true, 'url' => 'tool/image/upload', 'act' => 'default'),
                    'delete' => array('name' => '删除图片', 'checked' => false, 'display' => true, 'url' => 'tool/image/delete', 'act' => 'default'),
                ),
            ),
        ),
    ),
);