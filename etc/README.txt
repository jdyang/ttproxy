启动ttproxy
必须创建一个空文件，用来搭档进程共享
cfg.xml 配置搭档文件全路径 比如 /opt/www/ttproxy/etc/ttproxy.pid
启动必须用全路径
比如 /opt/www/ttproxy/bin/ttproxy /opt/www/ttproxy/etc/cfg.xml
不用监控进程脚本，进程down后搭档进程会自动起来