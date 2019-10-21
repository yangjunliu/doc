### install
```
tar zxvf DenyHosts-2.6.tar.gz                                           #解压源码包
cd DenyHosts-2.6                                                            #进入安装解压目录
python setup.py install                                                    #安装DenyHosts
cd /usr/share/denyhosts/                                                #默认安装路径
cp denyhosts.cfg-dist denyhosts.cfg                                #denyhosts.cfg为配置文件
cp daemon-control-dist daemon-control                        #daemon-control为启动程序
chown root daemon-control                                           #添加root权限
chmod 700 daemon-control                                            #修改为可执行文件
ln -s /usr/share/denyhosts/daemon-control /etc/init.d     #对daemon-control进行软连接，方便管理
```
### config
```
/etc/init.d/daemon-control start          #启动denyhosts
chkconfig daemon-control on             #将denghosts设成开机启动

vi /usr/share/denyhosts/denyhosts.cfg       #编辑配置文件，另外关于配置文件一些参数，通过grep -v "^#" denyhosts.cfg查看
SECURE_LOG = /var/log/secure                  #ssh 日志文件，redhat系列根据/var/log/secure文件来判断；Mandrake、FreeBSD根据 /var/log/auth.log来判断
#SUSE则是用/var/log/messages来判断，这些在配置文件里面都有很详细的解释。
HOSTS_DENY = /etc/hosts.deny                 #控制用户登陆的文件
PURGE_DENY = 30m                                  #过多久后清除已经禁止的，设置为30分钟；
# ‘m’ = minutes
# ‘h’ = hours
# ‘d’ = days
# ‘w’ = weeks
# ‘y’ = years
BLOCK_SERVICE = sshd                           #禁止的服务名，当然DenyHost不仅仅用于SSH服务
DENY_THRESHOLD_INVALID = 1             #允许无效用户失败的次数
DENY_THRESHOLD_VALID = 3                 #允许普通用户登陆失败的次数
DENY_THRESHOLD_ROOT = 3                 #允许root登陆失败的次数
DAEMON_LOG = /var/log/denyhosts      #DenyHosts日志文件存放的路径，默认
```
### common question
```
/etc/init.d/daemon-control restart         #重启denyhosts
starting DenyHosts: /usr/bin/env python /usr/bin/denyhosts.py --daemon --config=/usr/share/denyhosts/denyhosts.cfg
DenyHosts could not obtain lock (pid: )
[Errno 17] File exists: '/var/lock/subsys/denyhosts'

rm -f /var/lock/subsys/denyhosts
/etc/init.d/daemon-control restart 
starting DenyHosts: /usr/bin/env python /usr/bin/denyhosts.py –daemon –config=/usr/share/denyhosts/denyhosts.cfg
```
### answer
启动完成啦。
你可以使用
service denyhosts status来查看运行状态
DenyHosts is running with pid = 25874 表示已经启动起来了。
接下来就可以使用
cat /etc/hosts.deny来查看记录了
#service denyhost start
starting DenyHosts: /usr/bin/env python /usr/bin/denyhosts.py –daemon –config=/usr/share/denyhosts/denyhosts.cfg
python: can’t open file ‘/usr/bin/denyhosts.py’: [Errno 2] No such file or directory
经过查找发现denyhosts.py在目录/usr/local/bin/目录下，于是修改daemon-control文件
#vi daemon-control
DENYHOSTS_BIN = “/usr/bin/denyhosts.py”
DENYHOSTS_LOCK = “/var/lock/subsys/denyhosts”
DENYHOSTS_CFG = “/usr/share/denyhosts/denyhosts.cfg”

将第一行修改为DENYHOSTS_BIN = “/usr/local/bin/denyhosts.py”
在运行还会提示错误：导入Python版本错误的提示。如：
Traceback (most recent call last):
File “/usr/local/bin/denyhosts.py”, line 5, in
import DenyHosts.python_version
ImportError: No module named DenyHosts.python_version
到这里错误很明了了，经过查询发现版本不对会导致这个问题。
分析后发现原因在此：以前本机已经有一个python2.4的版本，使用rpm安装的，默认的路径是/usr/lib/python2.4，因为要升级python到2.5，也没有对卸载这个2.4的版本，使用编译安装的python2.5，安装路径并没有配置，这默认安装到/usr/local/lib/python2.5这个路径，而目前激活的python环境是2.5的，可能因为denyhosts安装时会根据环境查找安装，因此会在/usr/local/lib/python2.5/site-packages路径下安装Denyhosts这个文件夹。当运行denyhosts时，脚本会指定使用的是/usr/lib/python*这个路径的python里（暂时没找到脚本哪个地方指定），因此它无法定位python的版本，会出现这个错误。
最快速的解决方法是把/usr/local/lib/python2.5/site-packages路径下的Denyhosts文件夹整个拷贝到2.4的安装目录下即可。
进入/usr/local/lib/python2.5/site-packages/目录#cd /usr/local/lib/python2.5/site-packages/
#cp –rp Denyhosts /usr/lib/python2.4/ site-packages/

这样之后便可以启动Denyhosts了。

关于错误
Traceback (most recent call last):
File “/usr/local/python-2.4/bin/denyhosts.py”, line 5, in ?
import DenyHosts.python_version
ImportError: No module named DenyHosts.python_version
需要修改下面的部分：
1、/usr/share/denyhosts/daemon-control
PYTHON_BIN = “/usr/bin/env python”
改为
PYTHON_BIN = “/usr/local/python-2.4/bin/python”
#!/usr/bin/env python
改为
#!/usr/local/python-2.4/bin/python
DENYHOSTS_BIN = “/usr/bin/denyhosts.py”
改为
DENYHOSTS_BIN = “/usr/local/python-2.4/bin/denyhosts.py”
2.
cp -rp /usr/local/python-2.4/lib/python2.4/site-packages/DenyHosts/ /usr/local/python-2.4/lib/python2.4/
