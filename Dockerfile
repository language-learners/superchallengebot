FROM php:5.4-apache

RUN apt-get update && \
    apt-get install -y php5-mysqlnd git zlib1g-dev imagemagick libjpeg-dev libpng-dev \
        mysql-client && \
docker-php-ext-install zip mysql mysqli gd 


ADD ./superchallengebot/ /var/www/html/
ADD ./dockerconfig/config-include.php /var/www/html/configuration.php



# RUN mv /var/www/html/install /var/www/html/install-hidden && \
#     chmod +x composer.phar && \
#     ./composer.phar install && \
# rm -f composer.phar

