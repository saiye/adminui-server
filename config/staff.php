<?php

return array(
    'main' => array('name' => '系统设置', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            'sys' => array('name' => '权限设置', 'checked' => false, 'display' => true, 'act' => 'login', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'role-list' => array('name' => '角色列表', 'checked' => false, 'display' => true, 'url' => 'main/sys/role-list', 'act' => 'default'),
                    'edit-role' => array('name' => '编辑角色', 'checked' => false, 'display' => false, 'url' => 'main/sys/edit-role', 'act' => 'default'),
                    'add-role' => array('name' => '添加角色', 'checked' => false, 'display' => true, 'url' => 'main/sys/add-role', 'act' => 'default'),
                    'user-list' => array('name' => '用户列表', 'checked' => false, 'display' => true, 'url' => 'main/sys/user-list', 'act' => 'default'),
                    'edit-user' => array('name' => '编辑用户', 'checked' => false, 'display' => false, 'url' => 'main/sys/edit-user', 'act' => 'default'),
                    'add-user' => array('name' => '添加用户', 'checked' => false, 'display' => true, 'url' => 'main/sys/add-user', 'act' => 'default'),
                    'lock-user' => array('name' => '锁定用户', 'checked' => false, 'display' => false, 'url' => 'main/sys/lock-user', 'act' => 'default'),
                ),
            )
        ),
    ),
    'store' => array('name' => '门店管理', 'checked' => false, 'display' => true, 'act' => 'login', 'class' => 'fa fa-share',
        'child' => array(
            'index' => array('name' => '门店列表', 'checked' => false, 'display' => true, 'act' => 'login', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'storeList' => array('name' => '门店列表', 'checked' => false, 'display' => true, 'url' => 'store/index/storeList', 'act' => 'default'),
                    'addStore' => array('name' => '添加门店', 'checked' => false, 'display' => true, 'url' => 'store/index/addStore', 'act' => 'default'),
                    'editStore' => array('name' => '编辑门店', 'checked' => false, 'display' => true, 'url' => 'store/index/editStore', 'act' => 'default'),
                    'checkStore' => array('name' => '审核门店', 'checked' => false, 'display' => true, 'url' => 'store/index/checkStore', 'act' => 'default'),
                    'tagList' => array('name' => '门店标签', 'checked' => false, 'display' => true, 'url' => 'store/index/tagList', 'act' => 'default'),
                )
            )
        ),
    ),
    'company' => array('name' => '商户管理', 'checked' => false, 'display' => true, 'act' => 'login', 'class' => 'fa fa-share',
        'child' => array(
            'index' => array('name' => '商户列表', 'checked' => false, 'display' => true, 'act' => 'login', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'getState' => array('name' => '国家列表', 'checked' => false, 'display' => true, 'url' => 'company/index/getState', 'act' => 'login'),
                    'areaList' => array('name' => '地区列表', 'checked' => false, 'display' => true, 'url' => 'company/index/areaList', 'act' => 'login'),
                    'companyDetail' => array('name' => '商户详情', 'checked' => false, 'display' => true, 'url' => 'company/index/areaList', 'act' => 'default'),
                )
            )
        ),
    ),
   'game' => array('name' => '游戏管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            /*
            'index' => array('name' => '会员管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'userList' => array('name' => '会员列表', 'checked' => false, 'display' => true, 'url' => 'game/index/userList', 'act' => 'default'),
                    'addUser' => array('name' => '添加会员', 'checked' => false, 'display' => true, 'url' => 'game/index/addUser', 'act' => 'default'),
                    'editUser' => array('name' => '编辑会员', 'checked' => false, 'display' => true, 'url' => 'game/index/editUser', 'act' => 'default'),
                    'lockUser' => array('name' => '锁定会员', 'checked' => false, 'display' => true, 'url' => 'game/index/lockUser', 'act' => 'default'),
                ),
            ),
            */
           'board' => array('name' => '板子管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'boardList' => array('name' => '板子列表', 'checked' => false, 'display' => true, 'url' => 'game/board/boardList', 'act' => 'login'),
                  //  'addBoard' => array('name' => '添加板子', 'checked' => false, 'display' => true, 'url' => 'game/board/addBoard', 'act' => 'default'),
                   // 'editBoard' => array('name' => '编辑板子', 'checked' => false, 'display' => true, 'url' => 'game/board/editBoard', 'act' => 'default'),
                ),
            ),
        ),
    ),
    'order' => array('name' => '订单管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            'index' => array('name' => '订单管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'orderList' => array('name' => '订单列表', 'checked' => false, 'display' => true, 'url' => 'order/index/orderList', 'act' => 'default'),
                    'conf' => array('name' => '搜索条件', 'checked' => false, 'display' => true, 'url' => 'order/index/conf', 'act' => 'login'),
                    'detail' => array('name' => '订单详情', 'checked' => false, 'display' => true, 'url' => 'order/index/detail', 'act' => 'default'),
                    'set' => array('name' => '订单设置', 'checked' => false, 'display' => true, 'url' => 'order/index/set', 'act' => 'default'),
                    'findOrder' => array('name' => '查询订单', 'checked' => false, 'display' => true, 'url' => 'order/index/findOrder', 'act' => 'default'),
                    'refundApply' => array('name' => '退款申请', 'checked' => false, 'display' => true, 'url' => 'order/index/refundApply', 'act' => 'default'),
                    'createOrder' => array('name' => '后台下单', 'checked' => false, 'display' => true, 'url' => 'order/index/createOrder', 'act' => 'default'),
                    'refundApplyList' => array('name' => '退款申请列表', 'checked' => false, 'display' => true, 'url' => 'order/index/refundApplyList', 'act' => 'default'),
                    'agreeRefund' => array('name' => '同意退款', 'checked' => false, 'display' => true, 'url' => 'order/index/agreeRefund', 'act' => 'default'),
                    'refundConf' => array('name' => '退款配置', 'checked' => false, 'display' => true, 'url' => 'order/index/refundConf', 'act' => 'login'),
                ),
            ),
            'withdraw' => array('name' => '提现管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'orderList' => array('name' => '订单列表', 'checked' => false, 'display' => true, 'url' => 'order/withdraw/orderList', 'act' => 'default'),
                    'setStatus' => array('name' => '审核订单', 'checked' => false, 'display' => true, 'url' => 'order/withdraw/setStatus', 'act' => 'default'),
                    'addReceiptAccount' => array('name' => '添加收款账号', 'checked' => false, 'display' => true, 'url' => 'order/withdraw/addReceiptAccount', 'act' => 'default'),
                    'editReceiptAccount' => array('name' => '编辑收款账号', 'checked' => false, 'display' => true, 'url' => 'order/withdraw/editReceiptAccount', 'act' => 'default'),
                    'receiptAccountList' => array('name' => '收款账号列表', 'checked' => false, 'display' => true, 'url' => 'order/withdraw/receiptAccountList', 'act' => 'default'),
                    'conf' => array('name' => '搜索条件', 'checked' => false, 'display' => true, 'url' => 'order/withdraw/conf', 'act' => 'login'),
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
                    'companyAndRoomList' => array('name' => '商户，房间关联菜单', 'checked' => false, 'display' => true, 'url' => 'room/index/companyAndRoomList', 'act' => 'login'),
                ),
            ),
            'billing' => array('name' => '计费设置', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'billingList' => array('name' => '计费列表', 'checked' => false, 'display' => true, 'url' => 'room/billing/billingList', 'act' => 'default'),
                    'addBilling' => array('name' => '添加计费模式', 'checked' => false, 'display' => true, 'url' => 'room/billing/addBilling', 'act' => 'default'),
                    'billingConfig' => array('name' => '计费选择', 'checked' => false, 'display' => true, 'url' => 'room/billing/billingConfig', 'act' => 'login'),
                ),
            ),
            'device' => array('name' => '设备管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'deviceList' => array('name' => '计费列表', 'checked' => false, 'display' => true, 'url' => 'room/device/deviceList', 'act' => 'default'),
                ),
            ),
        ),
    ),

    'goods' => array('name' => '商城管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            'index' => array('name' => '商品管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'list' => array('name' => '商品列表', 'checked' => false, 'display' => true, 'url' => 'goods/index/list', 'act' => 'default'),
                    'add' => array('name' => '商品添加', 'checked' => false, 'display' => true, 'url' => 'goods/index/add', 'act' => 'default'),
                    'edit' => array('name' => '商品编辑', 'checked' => false, 'display' => true, 'url' => 'goods/index/edit', 'act' => 'default'),
                    'stock' => array('name' => '商品售罄', 'checked' => false, 'display' => true, 'url' => 'goods/index/stock', 'act' => 'default'),
                    'status' => array('name' => '商品上下架', 'checked' => false, 'display' => true, 'url' => 'goods/index/status', 'act' => 'default'),
                ),
            ),
            'cat' => array('name' => '商品管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'list' => array('name' => '商品分类列表', 'checked' => false, 'display' => true, 'url' => 'goods/cat/list', 'act' => 'default'),
                    'add' => array('name' => '添加商品分类', 'checked' => false, 'display' => true, 'url' => 'goods/cat/add', 'act' => 'default'),
                    'edit' => array('name' => '修改商品分类', 'checked' => false, 'display' => true, 'url' => 'goods/cat/edit', 'act' => 'default'),
                    'del' => array('name' => '删除商品分类', 'checked' => false, 'display' => true, 'url' => 'goods/cat/del', 'act' => 'default'),
                    'move' => array('name' => '移动商品,到其他分类', 'checked' => false, 'display' => true, 'url' => 'goods/cat/move', 'act' => 'default'),
                ),
            ),
            'quick' => array('name' => '商品快速标签管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'list' => array('name' => '标签列表', 'checked' => false, 'display' => true, 'url' => 'goods/quick/list', 'act' => 'login'),
                    'add' => array('name' => '添加标签', 'checked' => false, 'display' => true, 'url' => 'goods/quick/add', 'act' => 'login'),
                ),
            ),
        ),
    ),
    'tool' => array('name' => '全局功能管理', 'checked' => false, 'display' => true, 'act' => 'default', 'class' => 'fa fa-share',
        'child' => array(
            'image' => array('name' => '图片上传', 'checked' => false, 'display' => false, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'upload' => array('name' => '上传图片', 'checked' => false, 'display' => true, 'url' => 'tool/image/upload', 'act' => 'login'),
                    'delete' => array('name' => '删除图片', 'checked' => false, 'display' => true, 'url' => 'tool/image/delete', 'act' => 'login'),
                ),
            ),
            'goods' => array('name' => '商品图片上传', 'checked' => false, 'display' => false, 'act' => 'default', 'class' => 'fa fa-circle-o',
                'child' => array(
                    'upload' => array('name' => '商品图上传', 'checked' => false, 'display' => true, 'url' => 'tool/goods/upload', 'act' => 'login'),
                    'delete' => array('name' => '商品图删除', 'checked' => false, 'display' => true, 'url' => 'tool/goods/delete', 'act' => 'login'),
                ),
            ),
        ),
    ),
);
