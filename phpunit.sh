#!/usr/bin/env bash

# 启动单元测试
php -d zend_extension=xdebug.so vendor/phpunit/phpunit/phpunit
