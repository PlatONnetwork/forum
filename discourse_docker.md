    #安装docker
	   $ sudo apt update
       $ sudo apt  install docker.io
	   $ docker -v

	#安装git
	   $ sudo apt update
	   $ sudo apt install git
	#安装disocurse
	   $ sudo -s
	   $ git clone https://github.com/discourse/discourse_docker.git /var/discourse
	   $ cd /var/discourse
	#启动安装工具
	   $ ./discourse-setup
	   
	   配置账号、邮箱
	   Hostname for your Discourse? [discourse.example.com]: 
       Email address for admin account(s)? [me@example.com,you@example.com]: 
       SMTP server address? [smtp.example.com]: 
       SMTP port? [587]: 
       SMTP user name? [user@example.com]: 
       SMTP password? [pa$$word]: 
       Let's Encrypt account email? (ENTER to skip) [me@example.com]: 
	   
	   
	   配置文件位置：  /containers/app.yml  （修改后需执行 ./launcher rebuild app 生效配置置）
	   
	   重新创建容器：  ./launcher rebuild app
	   启动容器：  ./launcher start app
	   关闭容器：  ./launcher stop app
	   重启容器：  ./launcher restart app
	   删除容器：  ./launcher destroy app
	   在容器内开启终端： ./launcher enter app
	   检测 discourse：   ./discourse-doctor 
	   
	#SSL证书配置
	   证书文件位置：
	   /var/discourse/shared/standalone/ssl
	   私钥命名为：(文件名不能随意更改)
	   /var/discourse/shared/standalone/ssl/ssl.key
	   证书命名为：(文件名不能随意更改)
	   /var/discourse/shared/standalone/ssl/ssl.crt
	   
	   配置app.yml
	   $ vim /var/discourse/containers/app.yml
		templates:
		  - "templates/postgres.template.yml"
		  - "templates/redis.template.yml"
		  - "templates/web.template.yml"
		  - "templates/web.ratelimited.template.yml"
		## 取消下行注释添加证书 (https)
		  - "templates/web.ssl.template.yml"
		  #- "templates/web.letsencrypt.ssl.template.yml"
		  
		## 暴露443端口
		expose:
		  - "80:80"   # http
		  - "443:443" # https  
		  
	    ##容器内/shared 目录挂载路径
		volumes:
		  - volume:
			  host: /var/discourse/shared/standalone
			  guest: /shared
		  - volume:
			  host: /var/discourse/shared/standalone/log/var-log
			  guest: /var/log
		## 注释Let's Encrypt证书
		#LETSENCRYPT_ACCOUNT_EMAIL: example@example.com
				
		##重建容器
		./launcher rebuild app	  
		
		
	#使用Let's Encrypt 添加https证书
	   配置app.yml
	   $ vim /var/discourse/containers/app.yml
	   
	    templates:
		  - "templates/postgres.template.yml"
		  - "templates/redis.template.yml"
		  - "templates/web.template.yml"
		  - "templates/web.ratelimited.template.yml"
		## 取消下两行注释添加证书 (https)
		  - "templates/web.ssl.template.yml"
		  - "templates/web.letsencrypt.ssl.template.yml"
        
		
		## 暴露443端口
		expose:
		  - "80:80"   # http
		  - "443:443" # https

		## 给 Let’s Encrypt 添加个注册用邮箱账号。使用let's Encrype 免费证书（有效期3个月，系统自动免费续期）
		LETSENCRYPT_ACCOUNT_EMAIL: example@example.com
		
		##重建容器
		./launcher rebuild app

	

			   