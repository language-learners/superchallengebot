FROM php:5.4-apache

RUN apt-get update && \
    apt-get install -y php5-mysqlnd git zlib1g-dev imagemagick libjpeg-dev libpng-dev \
        mysql-client && \
docker-php-ext-install zip mysql mysqli gd

COPY ./ /var/www/html/

# Amazon CodePipeline / CodeBuild is actually pretty terrible and drops
# execute bits somewhere during the build process. So let's fix this here.
RUN chmod +x /var/www/html/worker
