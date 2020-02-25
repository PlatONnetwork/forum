##### Discourse提供单点登录可在官网设置论坛登录入口，论坛登录后，官网return_sso_url 路径会收到登录回执信息。

#### 开启 sso provider
##### 1、进入论坛后台 > 设置 > 登录
##### 2、开启 enable sso provider
##### 3、填写 sso provider secrets (官网域名和SSO密钥)
#### 从下方获取代码 （已附带 php 实例）测试安装
###### https://meta.discourse.org/t/using-discourse-as-a-sso-provider/32974/95
###### 各语言代码实例连接
    Go:
    https://github.com/sekhat/godiscuss/blob/master/sso/sso.go 
    PHP:
    https://gist.github.com/paxmanchris/e93018a3e8fbdfced039
    Ruby:
    https://github.com/gogo52cn/sso_with_discourse
    Erlang:
    https://github.com/reverendpaco/discourse-as-sso-erlang	    
    Node.js:
    https://github.com/edhemphill/passport-discourse
    ASP.NET 
    https://github.com/Biarity/DiscourseSso