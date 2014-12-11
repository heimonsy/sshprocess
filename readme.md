解决执行SSH远程命令和rsync的登录输入密码的问题，可以通过配置好ssh key。而无须在交互模式下手动输入。

依赖expect
安装expect
```bash
# ubuntu/debain
sudo apt-get install expect
# centos
yum install expect
# osx
brew install expect
```
