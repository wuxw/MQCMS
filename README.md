# MQCMS
MQCMS是一款现代化，快速，高效，灵活，前后端分离，扩展性强的CMS系统。
MQCMS中的MQ取麻雀拼音首字母。寓意麻雀虽小五脏俱全。
### 特别感谢
本项目基于hyperf框架开发的应用，感谢hyperf的作者提供了这么优秀的框架

### 开发文档
文档正在在路上...

前端项目仓库：
https://github.com/MQEnergy/MQCMS-admin

### 本地开发
在docker环境下开发，window10环境安装`docker desktop for window`,
window10以下环境安装`docker toolbox`。


下载hyperf框架docker镜像
```
docker pull hyperf/hyperf
```

进入docker运行命令：
```
# 例如：将项目放在本地d:/web/mqcms
docker run -it -v /d/web/mqcms:/mqcms -p 9501:9501 --entrypoint /bin/sh hyperf/hyperf
```

下载mqcms系统
```
git clone https://github.com/MQEnergy/MQCMS mqcms
```

将 Composer 镜像设置为阿里云镜像，加速国内下载速度
```
php mqcms/bin/composer.phar config -g repo.packagist composer https://mirrors.aliyun.com/composer

```

docker安装redis
```
docker pull redis
# 进入redis 配置redis可外部访问

docker ps -a
docker exec -it [redis的CONTAINER ID] /bin/sh
vi /etc/redis.conf

# 修改bind如下（根据自己熟悉程度配置）
# bind 0.0.0.0

# 可开启password（自行按需修改）
# requirepass foobared
```

进入项目安装依赖启动项目
```
cd mqcms
php bin/composer.phar install
cp .env.example .env
php bin/hyperf.php start
```

浏览器访问项目
```
http://127.0.0.1:9501
```

### 扩展功能
#### command命令扩展
1、创建service
```
# 查看mq:service命令帮助
php bin/hyperf.php mq:service --help

# 创建App\Service命名空间的service
php bin/hyperf.php mq:service FooService Foo
# FooAdminService：service名称 FooAdmin：model名称
 
# 创建其他命名空间的service
php bin/hyperf.php mq:service -N App\\Service\\Admin FooAdminService FooAdmin
# FooAdminService：service名称 FooAdmin：model名称
 
```

2、创建controller
```
# 查看mq:controller命令帮助
php bin/hyperf.php mq:controller --help

# 创建App\Controller命名空间的controller
php bin/hyperf.php mq:controller FooController FooService admin
# FooController：controller名称 FooService：service名称 admin：模块名称（后台，接口 可扩展，eg.可写成：Admin ADMIN admin ...）

# 创建其他命名空间的controller
php bin/hyperf.php mq:controller -N App\\Controller\\Admin\\V1 FooController FooService api
# FooController：controller名称 FooService：service名称 api：模块名称（后台，接口 可扩展，eg.可写成：Api API api ...）

```