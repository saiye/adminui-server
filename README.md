
## About LRSMANAGE
 基于laravel7.x,环境要求如下,建议上最新版php7.4
 ###必须：
 - PHP >= 7.2.5
 - BCMath PHP Extension
 - Ctype PHP Extension
 - Fileinfo PHP extension
 - JSON PHP Extension
 - Mbstring PHP Extension
 - OpenSSL PHP Extension
 - PDO PHP Extension
 - Tokenizer PHP Extension
 - XML PHP Extension
  ###可选：
- swoole PHP Extension
- redis PHP Extension

##Install Project
因为项目基于composer管理依赖，需要进入目录，执行以下命令安装依赖。
```text
依赖安装
composer update 
配置文件.env,可以执行下面命令生成!
cp .env.example  .env
生成安全key
php artisan key:generate 
数据库初始化
php artisan migrate
数据迁移,初始化管理账号
php artisan db:seed

本地测试环境,可运行以下:
php artisan serve
可以通过访问后台
 http://127.0.0.1:8000
访问，此模式只能在测试环境使用！
后台账号，密码可以配置.env文件的
AdminEmail=xxx@qq.com
UserName=admin
AdminPassword=123456

开发者管理员账号，
应该在role配置文件配置super_admin列表
否则登录后台，因初始化时权限未设置，无法查看任何功能。


线上环境，建议配置ngixn+php-fpm模式:
Nginx

server
    {
        listen 80;
        server_name  www.game.com;
        index index.html index.htm index.php;
        root  /mnt/web/manage/public;
	    location / {
          try_files $uri $uri/ /index.php?$query_string;
        }

        include enable-php.conf;

        location /nginx_status
        {
            stub_status on;
            access_log   off;
        }

        location ~ /\.
        {
            deny all;
        }

}
```

```







