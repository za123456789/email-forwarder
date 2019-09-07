#!/bin/bash
cp /var/www/.env.example /var/www/.env
chown www-data:www-data /var/www/ -R
chmod 777 /var/www/storage/logs/ -R
chmod 777 /email/ -R
php-fpm
