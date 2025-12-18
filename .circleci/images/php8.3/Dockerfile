FROM cimg/php:8.3-browsers

RUN sudo apt update
RUN sudo -E install-php-extensions zip mysqli pdo_mysql
RUN sudo apt install -y bash git ssh tar gzip ca-certificates mariadb-client libu2f-udev firefox
RUN sudo pecl install xdebug