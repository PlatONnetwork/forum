##### docker 安装方式
1、配置`app.yml` 
```
    hooks:
      after_code:
        - exec:
            cd: $home/plugins
            cmd:
              - mkdir -p plugins
              - git clone https://github.com/discourse/docker_manager.git
              - git clone https://github.com/discourse/discourse-math.git
```
2、重构容器
```
$ ./launcher rebuild app
```
##### 容器内安装方式
1、进入容器
```
$ sudo docker exec -it app /bin/bash
```
2、安装插件
```
$ su discourse 
$ cd /var/www/discourse 
$ RAILS_ENV=production bundle exec rake plugin:install repo=https://github.com/discourse/discourse-math.git
$ RAILS_ENV=production bundle exec rake assets:precompile

```
###### 退出容器
3、重启容器
```
$ ./launcher restart app
```

##### 启用插件

管理员后台 》插件 ， 选择 discourse-math 的设置选项，选择 discourse math enabled 启用插件。