#!/usr/bin/env php
<?php

//Определяем DOCUMENT_ROOT - без него битрикс не работает
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/web');
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

//Проверяем, чтобы использование которткого синтаксиса тегов было включено
if ((int) ini_get('short_open_tag') === 0) {
    $phpini = php_ini_loaded_file();
    echo "\033[31m";
    echo '    short_open_tag parameter is off:';
    echo "\n\r        1. set `short_open_tag=On` in `{$phpini}`";
    echo "\n\r        2. or run command like follow `php -d short_open_tag=On -f cli.php`";
    echo "\033[0m \n\r\n\r";
    die();
}

//Проверяем на месте ли сам битрикс
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php')) {
    echo "\033[31m";
    echo "    File `{$_SERVER['DOCUMENT_ROOT']}/bitrix/modules/main/include/prolog_before.php` not found";
    echo "\n\r    Please check bitrix installation";
    echo "\033[0m \n\r\n\r";
    die();
}

//Отключаем сбор статистики и выполнение агентов
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_AGENT_CHECK', true);
define('PERFMON_STOP', true);

//Подключаем пролог битрикса
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

//Проверяем существование symfony console
if (!class_exists('\Symfony\Component\Console\Application')) {
    echo "\033[31m";
    echo "    Class `\Symfony\Component\Console\Application` not found:";
    echo "\n\r    1. please check that you ran `composer install`";
    echo "\n\r    2. please check that `vendor/autoload.php` is included";
    echo "\033[0m \n\r\n\r";
    die();
}

//Инициируем symfony console
$application = new \Symfony\Component\Console\Application('Console runner for Bitrix');

//Команды миграций
if (class_exists(\marvin255\bxmigrate\cli\Factory::class)) {
    $migrations = __DIR__ . '/migrations';
    \marvin255\bxmigrate\cli\Factory::registerCommands($application, $migrations);
}

//Команды кодогенератора
if (class_exists(\marvin255\bxcodegen\Factory::class)) {
    \marvin255\bxcodegen\Factory::registerCommands($application, __DIR__ . '/.bxcodegen.yaml');
}

//Команды проекта
$application->add(new \app\base\console\CacheClear);
$application->add(new \app\base\console\HashRead);
$application->add(new \app\base\console\HashWrite);

$application->run();
