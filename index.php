<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
mb_internal_encoding('UTF-8'); // Кодировка по умолчанию
ini_set('display_errors', 1);
$config = include 'config.php';
/**
 * Подключение к базе данных
 */
include 'lib/database/DataBase.php';
$db = DataBase::connect(
    $config['mysql']['host'],
    $config['mysql']['dbname'],
    $config['mysql']['user'],
    $config['mysql']['pass']
);
include 'lib/router/router.php';
//include 'template/user/register.php';