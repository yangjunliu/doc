[NGINx]
docker pull nginx
docker run -p 80:80 -p 8080:8080 -p 8090:8090 -p 8081:8081 -p 8091:8091 --name nginx \
-v /data/docker/nginx/nginx.conf:/etc/nginx/nginx.conf \
-v /data/docker/nginx/logs:/var/log/nginx \
-v /data/docker/nginx/conf.d:/etc/nginx/conf.d \
-v /data/project_sdk:/var/www/html \
-d nginx


[mysql:5.7]
docker pull mysql:5.7
sudo docker run -p 3306:3306 --name mysql:5.7 \
-v /data/docker/mysql5.7/logs:/var/log/mysql \
-v /data/docker/mysql5.7/data:/var/lib/mysql \
-v /data/docker/mysql5.7/conf:/etc/mysql \
-e MYSQL_ROOT_PASSWORD=Pass@123 \
-d mysql:5.7


[PHP]
Dockerfile

FROM php:7.3.8-fpm

RUN mv /etc/apt/sources.list /etc/apt/sources.list.bak \
    && echo 'deb http://mirrors.aliyun.com/debian/ buster main non-free contrib' > /etc/apt/sources.list \
    && echo 'deb http://mirrors.aliyun.com/debian-security buster/updates main' >> /etc/apt/sources.list \
    && echo 'deb http://mirrors.aliyun.com/debian/ buster-updates main non-free contrib' >> /etc/apt/sources.list \
    && echo 'deb http://mirrors.aliyun.com/debian/ buster-backports main non-free contrib' >> /etc/apt/sources.list \
    && apt-get update \
    && export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" \
    && apt-get install -y --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libpng-dev libmagickwand-dev libmcrypt-dev libmemcached-dev zlib1g-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) bcmath calendar exif gettext sockets dba mysqli pcntl pdo_mysql shmop sysvmsg sysvsem sysvshm iconv gd \
    && pecl install imagick-3.4.4 mcrypt-1.0.2 memcached-3.1.3 redis-5.0.2 xdebug-2.7.2 \
    && docker-php-ext-enable imagick mcrypt memcached redis xdebug \
    && docker-php-ext-configure opcache --enable-opcache && docker-php-ext-install opcache && docker-php-ext-install zip

LABEL Author="Stone"
LABEL Version="2022.6"
LABEL Description="PHP 7.3.8 开发环境镜像."


docker build -t php:7.38 .

docker run --rm --name php7.38 
docker cp php7.38:/usr/local/etc /data/docker/php7.38

docker run -p 9000:9000 --name php7.38 \
-v /data/project_sdk:/var/www/html:rw \
-v /data/docker/php7.3/etc:/usr/local/etc \
-v /data/docker/php7.3/logs:/var/log/php \
-v /etc/localtime:/etc/localtime:ro \
-d php:7.38

docker run \
-p 6379:6379 --name redis \
-v /data/docker/redis/redis.conf:/etc/redis/redis.conf \
-v /data/docker/redis/data:/data:rw \
--privileged=true -d redis redis-server /etc/redis/redis.conf \
--appendonly yes


docker run -p 7001:80 --name nginx-saier-site \
-v /data/docker/nginx-saier-site/nginx.conf:/etc/nginx/nginx.conf \
-v /data/docker/nginx-saier-site/logs:/var/log/nginx \
-v /data/docker/nginx-saier-site/conf.d:/etc/nginx/conf.d \
-v /data/project_sdk:/var/www/html \
-d nginx
