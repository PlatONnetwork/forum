#### 安装docker
```
$ sudo yum update
$ yum -y install yum-utils
```
##### 添加Docker源
```
$ yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
```
##### 更新源
```
$ yum makecache
$ yum list docker-ce --showduplicates | sort -r
```
##### 安装docker-ce
```
$ sudo yum install docker-ce-18.09.7 docker-ce-cli-18.09.7 containerd.io
```
##### 启动Docker
```
$ systemctl start docker
$ systemctl enable docker
```
##### 查看docker版本
```
$ docker version
```
#### 安装git
```
$ sudo yum install git
```
#### 安装disocurse
```
$ sudo -s
$ git clone https://github.com/discourse/discourse_docker.git /var/discourse
$ cd /var/discourse
```
#### 启动安装工具
```
$ ./discourse-setup
```
##### 配置账号、邮箱
````
Hostname for your Discourse? [discourse.example.com]:
Email address for admin account(s)? [me@example.com,you@example.com]:
SMTP server address? [smtp.example.com]:
SMTP port? [587]:
SMTP user name? [user@example.com]:
SMTP password? [pa$$word]:
Let's Encrypt account email? (ENTER to skip) [me@example.com]:
````

##### 配置文件位置：
###### containers/app.yml  （修改后需执行 ./launcher rebuild app 生效配置）
###### 重新创建容器：  ./launcher rebuild app
###### 启动容器：  ./launcher start app
###### 关闭容器：  ./launcher stop app
###### 重启容器：  ./launcher restart app
###### 删除容器：  ./launcher destroy app
###### 在容器内开启终端： ./launcher enter app
###### 检测 discourse：   ./discourse-doctor

#### ./launcher 命令执行出错参考方案
```
$ vim launcher
```
##### 注释以下代码
```
    # 6. able to attach stderr / out / tty
    #test=`$docker_path run $user_args -i --rm -a stdout -a stderr $image echo working`
    #if [[ "$test" =~ "working" ]] ; then : ; else
    #  echo "Your Docker installation is not working correctly"
    #  echo
    #  echo "See: https://meta.discourse.org/t/docker-error-on-bootstrap/13657/18?u=sam"
    #  exit 1
    #fi
```

#### SSL证书配置
###### 证书文件位置：
###### /var/discourse/shared/standalone/ssl
###### 私钥命名为：(文件名不能随意更改)
###### /var/discourse/shared/standalone/ssl/ssl.key
###### 证书命名为：(文件名不能随意更改)
###### /var/discourse/shared/standalone/ssl/ssl.crt


##### 配置app.yml
```
$ vim /var/discourse/containers/app.yml
```
```
templates:
- "templates/postgres.template.yml"
- "templates/redis.template.yml"
- "templates/web.template.yml"
- "templates/web.ratelimited.template.yml"
## 取消下行注释添加证书 (https)
- "templates/web.ssl.template.yml"
#- "templates/web.letsencrypt.ssl.template.yml"
```

##### 暴露443端口
```
expose:
- "80:80"   # http
- "443:443" # https
```

##### 容器内/shared 目录挂载路径
```
volumes:
- volume:
host: /var/discourse/shared/standalone
guest: /shared
- volume:
host: /var/discourse/shared/standalone/log/var-log
guest: /var/log
```
##### 注释Let's Encrypt证书
```
#LETSENCRYPT_ACCOUNT_EMAIL: example@example.com
```
##### 重建容器
```
./launcher rebuild app
```


#### 使用Let's Encrypt 添加https证书

##### 配置app.yml
```
$ vim /var/discourse/containers/app.yml
```
```
templates:
- "templates/postgres.template.yml"
- "templates/redis.template.yml"
- "templates/web.template.yml"
- "templates/web.ratelimited.template.yml"
## 取消下两行注释添加证书 (https)
- "templates/web.ssl.template.yml"
- "templates/web.letsencrypt.ssl.template.yml"
```

##### 暴露443端口
```
expose:
- "80:80"   # http
- "443:443" # https
```
```
## 给 Let’s Encrypt 添加个注册用邮箱账号。使用let's Encrype 免费证书（有效期3个月，系统自动免费续期）
LETSENCRYPT_ACCOUNT_EMAIL: example@example.com
```
##### 重建容器
```
./launcher rebuild app
```


#### 端口被占用时解决方案 1 (使用nginx代理 启动安装工具时需关闭 nginx )

##### 修改app.yml配置文件
```
expose:
  - "81:80"   # http
  - "444:443" # https
```
##### 添加 nginx 配置文件 discourse.config


```
server {
    listen  443 ssl;
    server_name SEVER_NAME(域名);
    ssl on;    #nginx版本不同配置方式有差异
    ssl_certificate      PATH(证书文件路径);
    ssl_certificate_key  PATH(证书文件路径);
    ssl_session_tickets off;
 location / {
        proxy_set_header  Host  $http_host;
        proxy_set_header  X-Real-IP  $remote_addr;
        proxy_set_header  X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_pass  https://127.0.0.1:444; (服务器IP地址)
    }
}

server {
    listen  80;
    server_name SEVER_NAME(域名);
    location / {
        proxy_set_header  Host  $http_host;
        proxy_set_header  X-Real-IP  $remote_addr;
        proxy_set_header  X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_pass  https://127.0.0.1:81;(服务器IP地址)
    }
}

```

##### 重建容器
```
$ ./launcher rebuild app
```

#### 端口被占用时解决方案 2 (使用 nginx 代理 启动安装工具时不需关闭 nginx )

##### 克隆 discourse_docker

```
$ sudo -s
$ git clone https://github.com/discourse/discourse_docker.git /var/discourse
$ cd /var/discourse
```

##### 准备一个 app.yml 配置文件 跳过 setup 时对 端口的判断

```
$ cp app.yml containers/app.yml
```

##### 修改app.yml配置文件

```
$ vim containers/app.yml



expose:
  - "81:80"   # http
  - "444:443" # https
```
  
##### 修改nginx 配置文件 

```
server {
    listen  80;
    server_name SEVER_NAME(域名);
    location / {
        proxy_set_header  Host  $http_host;
        proxy_set_header  X-Real-IP  $remote_addr;
        proxy_set_header  X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_pass  http://127.0.0.1:81;  #(http)
    }
}
```
##### 启动安装工具

```
$ ./discourse-setup
```
##### 查看证书是否申请成功

```
$ cd /var/discoruse/shared/standalone/ssl
$ ls -lh
```

##### 再次修改nginx 配置文件 

```
server {
    listen  443 default ssl;
    server_name SEVER_NAME(域名) ;
    ssl_certificate      PATH(证书文件路径);
    ssl_certificate_key  PATH(证书文件路径);
    ssl_session_tickets off;
	location / {
        proxy_set_header  Host  $http_host;
        proxy_set_header  X-Real-IP  $remote_addr;
        proxy_set_header  X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_pass  https://127.0.0.1:444;

    }

}

server {
    listen  80;
    server_name SEVER_NAME(域名) ;
    location / {
        proxy_set_header  Host  $http_host;
        proxy_set_header  X-Real-IP  $remote_addr;
        proxy_set_header  X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_pass  https://127.0.0.1:81;  #(https)
    }
}
```
##### 重新加载 nginx 配置，查看页面是否能够访问。



#### 快速修改配置参考方案一

##### discoruse 配置是在创建容器时通过添加环境变量的方式传进容器

##### 已经部署好项目后，进入容器
```
$ sudo docker exec -it app /bin/bash
```
##### 查看环境变量，可以修改的配置
```
$ env  
```
##### 修改启动文件
```
$ vim /sbin/boot
```
##### 添加需要修改的配置到文件顶部，保存
###### 例：修改邮箱端口
```
export DISCOURSE_SMTP_PORT=1234 
```
##### 退出容器
```
$ exit
```
##### 重启容器  （约10秒钟）
```
$ /var/discourse/launcher restart app
```

##### 页面查看 邮箱端口修改情况

###### https://DOMAIN_NAME/admin/email

##### 如果重建容器   /var/discourse/launcher rebuild app 执行前需修改配置文件 /var/discourse/containers/app.yml

#### 快速修改配置参考方案二

##### 停止并删除容器

```
$ sudo docker stop app && sudo docker rm app
```

##### 修改启动容器命令

```
$ sudo docker run --shm-size=512m -d --restart=always -e LANG=en_US.UTF-8 -e RAILS_ENV=production -e UNICORN_WORKERS=2 -e UNICORN_SIDEKIQS=1 -e RUBY_GLOBAL_METHOD_CACHE_SIZE=131072 -e RUBY_GC_HEAP_GROWTH_MAX_SLOTS=40000 -e RUBY_GC_HEAP_INIT_SLOTS=400000 -e RUBY_GC_HEAP_OLDOBJECT_LIMIT_FACTOR=1.5 -e DISCOURSE_DB_SOCKET=/var/run/postgresql -e DISCOURSE_DB_HOST= -e DISCOURSE_DB_PORT= -e LETSENCRYPT_DIR=/shared/letsencrypt -e DISCOURSE_HOSTNAME=www.eos.top -e DISCOURSE_DEVELOPER_EMAILS=1976335605@qq.com -e DISCOURSE_SMTP_ADDRESS=mail.newton.top -e DISCOURSE_SMTP_PORT=25 -e DISCOURSE_SMTP_USER_NAME=postmaster@newton.top -e DISCOURSE_SMTP_PASSWORD=Aphp9mdl8 -e LETSENCRYPT_ACCOUNT_EMAIL=me@example.com -h platon3-app -e DOCKER_HOST_IP=172.17.0.1 --name app -t -p 80:80 -p 443:443 -v /var/discourse/shared/standalone:/shared -v /var/discourse/shared/standalone/log/var-log:/var/log --mac-address 02:7d:e7:09:5d:83 local_discourse/app /sbin/boot
```
```
docker run [OPTIONS] IMAGE [COMMAND] [ARG...]

OPTIONS说明：

	-a stdin: 指定标准输入输出内容类型，可选 STDIN/STDOUT/STDERR 三项；

	-d: 后台运行容器，并返回容器ID；

	-i: 以交互模式运行容器，通常与 -t 同时使用；

	-P: 随机端口映射，容器内部端口随机映射到主机的高端口

	-p: 指定端口映射，格式为：主机(宿主)端口:容器端口

	-t: 为容器重新分配一个伪输入终端，通常与 -i 同时使用；

	--name="nginx-lb": 为容器指定一个名称；

	--dns 8.8.8.8: 指定容器使用的DNS服务器，默认和宿主一致；

	--dns-search example.com: 指定容器DNS搜索域名，默认和宿主一致；

	-h "mars": 指定容器的hostname；

	-e username="ritchie": 设置环境变量；

	--env-file=[]: 从指定文件读入环境变量；

	--cpuset="0-2" or --cpuset="0,1,2": 绑定容器到指定CPU运行；

	-m :设置容器使用内存最大值；

	--net="bridge": 指定容器的网络连接类型，支持 bridge/host/none/container: 四种类型；

	--link=[]: 添加链接到另一个容器；

	--expose=[]: 开放一个端口或一组端口；

	--volume , -v:	绑定一个卷
```
##### 如果重建容器   /var/discourse/launcher rebuild app 执行前需修改配置文件 /var/discourse/containers/app.yml