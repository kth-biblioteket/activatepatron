FROM php:7.3-apache

RUN a2enmod rewrite && \
cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini && \
docker-php-ext-install mysqli pdo pdo_mysql && \
apt-get update && \
apt-get -y install nano locales && \
sed -i '/en_GB.UTF-8/s/^# //g' /etc/locale.gen && \
    locale-gen && \
sed -i '/sv_SE.UTF-8/s/^# //g' /etc/locale.gen && \
locale-gen

ENV LANG en_GB.UTF-8
ENV LANGUAGE en_GB:en
ENV LC_ALL en_GB.UTF-8

# Disable error display in the custom php.ini file
RUN sed -i -e 's/^display_errors\s*=\s*On/display_errors = Off/g' $PHP_INI_DIR/php.ini

COPY ./src /var/www/html
