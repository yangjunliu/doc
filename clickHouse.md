### 安装
使用dockers安装
1. 拉去镜像
```
// 服务端
docker pull clickhouse/clickhouse-server
// 客户端
docker pull yandex/clickhouse-client
```
2. 创建挂载目录
```
// 创建目录
mkdir /data/clickhouse/conf
mkdir /data/clickhouse/log
mkdir /data/clickhouse/store
// 把docker容器中的配置文件复制到本地
docker run --rm -d --name=ch-server --ulimit nofile=262144:262144 clickhouse/clickhouse-server
sudo docker cp ch-server:/etc/clickhouse-server/config.xml /data/clickhouse/conf/config.xml
sudo docker cp ch-server:/etc/clickhouse-server/users.xml /data/clickhouse/conf/users.xml
// 停止容器
docker stop ch-server
```
3. 修改配置，挂在目录以及配置文件
```
// 可能会有权限问题，如果有权限问题就直接给777权限或者自己研究下
docker run -d --name=clickhouse-server \
-p 8123:8123 -p 9009:9009 -p 19090:9000 \
--ulimit nofile=262144:262144 \
-v /data/clickhouse/conf/config.xml:/etc/clickhouse-server/config.xml \
-v /data/clickhouse/conf/users.xml:/etc/clickhouse-server/users.xml \
-v /data/clickhouse/log:/var/log/clickhouse-server \
-v /data/clickhouse/store:/var/lib/clickhouse/store \
clickhouse/clickhouse-server
```
4. 命令好客户端使用
```
docker run -it --rm --name=clickhouse-client --link clickhouse-server:clickhouse-server clickhouse/clickhouse-client --host clickhouse-server
```
