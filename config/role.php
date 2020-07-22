<?php
return [
    /*
     * rabc,该类型路由忽略rabc权限，
     * */
    'rbac'=>['guest','login'],

    /*
      超级用户,忽略rabc权限过滤,一般是开发人员的email数组
   */
    'super_admin'=>['admin'],

    /**
     * 用户锁定状态
     */
    'lock'=>[
        0=>'未锁定',
        1=>'锁定',
    ],

];
