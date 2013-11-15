#!/bin/sh
# travis before_script

mysql -e 'create database cascade_test_on_travis;'
echo "extension = memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

php -r "if(version_compare(PHP_VERSION, '5.5.0', '>=')) {exit(0);} else {exit(255);}";

if [ $? -eq 0 ]; then
    yes | pecl install apcu-beta
    echo "extension = apcu.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
else
    echo "extension = apc.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi

echo "apc.enable_cli=1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

