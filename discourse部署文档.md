#### 数据库主机A、应用主机B

#### 主机A操作


#### 一、 安装postgresql 10

##### 1、更新源
```
$ yum install https://download.postgresql.org/pub/repos/yum/10/redhat/rhel-7-x86_64/pgdg-centos10-10-2.noarch.rpm -y 
```
##### 2、查看postgresql源
```
$ yum list | grep postgresql
```
##### 3、安装postgresql
```
$ yum install postgresql10-contrib postgresql10-server -y
```

##### 4、初始化数据库
```
$ /usr/pgsql-10/bin/postgresql-10-setup initdb
```
##### 5、启动数据库并设置开机启动
```
sudo systemctl start postgresql-10
sudo systemctl enable postgresql-10.service
```
#### 二、添加discourse账户、创建数据库
```
$ su postgres
$ psql
```
```
postgres=# CREATE USER discourse PASSWORD '密码';
postgres=# CREATE DATABASE discourse OWNER discourse;
postgres=# \c discourse
postgres=# CREATE EXTENSION hstore;
postgres=# CREATE EXTENSION pg_trgm;
postgres=# eixt;
```
#### 三、配置支持远程登录

##### 1、修改pg_hba.conf文件，配置用户的访问权限
```
$ vi /var/lib/pgsql/10/data/pg_hba.conf
```
```
host  all    all    0.0.0.0/0    md5
```
###### 保存退出

##### 2、修改postgresql.conf文件，将数据库服务器的监听模式修改为监听所有主机发出的连接请求
```
$ vi /var/lib/pgsql/10/data/postgresql.conf
```
```
#listen_addresses='localhost'
listen_addresses='*'
```
##### 3、重启postgresql
```
systemctl restart postgresql-10
```


#### 主机B操作

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

##### 添加配置文件

```
$ vim /var/discourse/containers/app.yml
```
###### 添加内容

```
## this is the all-in-one, standalone Discourse Docker container template
##
## After making changes to this file, you MUST rebuild
## /var/discourse/launcher rebuild app
##
## BE *VERY* CAREFUL WHEN EDITING!
## YAML FILES ARE SUPER SUPER SENSITIVE TO MISTAKES IN WHITESPACE OR ALIGNMENT!
## visit http://www.yamllint.com/ to validate this file as needed

templates:
#  - "templates/postgres.template.yml"
  - "templates/redis.template.yml"
  - "templates/web.template.yml"
  - "templates/web.ratelimited.template.yml"
## Uncomment these two lines if you wish to add Lets Encrypt (https)
  - "templates/web.ssl.template.yml"
  - "templates/web.letsencrypt.ssl.template.yml"

## which TCP/IP ports should this container expose?
## If you want Discourse to share a port with another webserver like Apache or nginx,
## see https://meta.discourse.org/t/17247 for details
expose:
  - "81:80"   # http
  - "444:443" # https

params:
  db_default_text_search_config: "pg_catalog.english"

  ## Set db_shared_buffers to a max of 25% of the total memory.
  ## will be set automatically by bootstrap based on detected RAM, or you can override
  db_shared_buffers: "128MB"

  ## can improve sorting performance, but adds memory usage per-connection
  #db_work_mem: "40MB"

  ## Which Git revision should this container use? (default: tests-passed)
  #version: tests-passed

env:
  LANG: en_US.UTF-8
  # DISCOURSE_DEFAULT_LOCALE: en
  DISCOURSE_DB_USERNAME: discourse
  DISCOURSE_DB_PASSWORD: ******          #主机A数据库discourse用户密码
  DISCOURSE_DB_HOST: ***.***.***.***     #主机AIP地址
  DISCOURSE_DB_NAME: discourse
  
## How many concurrent web requests are supported? Depends on memory and CPU cores.
  ## will be set automatically by bootstrap based on detected CPUs, or you can override
  UNICORN_WORKERS: 2

  ## TODO: The domain name this Discourse instance will respond to
  ## Required. Discourse will not work with a bare IP number.
  DISCOURSE_HOSTNAME: [discourse.example.com]  #论坛域名

  ## Uncomment if you want the container to be started with the same
  ## hostname (-h option) as specified above (default "$hostname-$config")
  #DOCKER_USE_HOSTNAME: true

  ## TODO: List of comma delimited emails that will be made admin and developer
  ## on initial signup example 'user1@example.com,user2@example.com'
  DISCOURSE_DEVELOPER_EMAILS: 'example@example.com'  #管理员邮箱

  ## TODO: The SMTP mail server used to validate new accounts and send notifications
  # SMTP ADDRESS, username, and password are required
  # WARNING the char '#' in SMTP password can cause problems!
  DISCOURSE_SMTP_ADDRESS: smtp.example.com     #邮箱服务器地址
  DISCOURSE_SMTP_PORT: 25
  DISCOURSE_SMTP_USER_NAME: user@example.com   #邮箱服务器用户名
  DISCOURSE_SMTP_PASSWORD: "*******"           #邮箱服务器密码
  #DISCOURSE_SMTP_ENABLE_START_TLS: true           # (optional, default true)

  ## If you added the Lets Encrypt template, uncomment below to get a free SSL certificate
  LETSENCRYPT_ACCOUNT_EMAIL: me@example.com

  ## The http or https CDN address for this Discourse instance (configured to pull)
  ## see https://meta.discourse.org/t/14857 for details
  #DISCOURSE_CDN_URL: https://discourse-cdn.example.com

## The Docker container is stateless; all data is stored in /shared
volumes:
  - volume:
      host: /var/discourse/shared/standalone
      guest: /shared
  - volume:
      host: /var/discourse/shared/standalone/log/var-log
      guest: /var/log

## Plugins go here
## see https://meta.discourse.org/t/19157 for details
hooks:
  after_code:
    - exec:
        cd: $home/plugins
        cmd:
          - git clone https://github.com/discourse/docker_manager.git

## Any custom commands to run after building
run:
  - exec: echo "Beginning of custom commands"
  ## If you want to set the 'From' email address for your first registration, uncomment and change:
  ## After getting the first signup email, re-comment the line. It only needs to run once.
  #- exec: rails r "SiteSetting.notification_email='info@unconfigured.discourse.org'"
  - exec: echo "End of custom commands"

```

###### 保存退出

 
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

